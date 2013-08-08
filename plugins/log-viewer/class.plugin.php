<?php
/*
 Tag: 2013.05.19
 Version: 2013.05.19
 Timestamp: 2013.05.19.0054
 */

if (!function_exists('add_action')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class ciLogViewer
{
    const DOMAIN = "ciLogViewer";
    private static $_pluginDirUrl = false;
    private $_file_view = false;
    private $_files = array();

    public function __construct()
    {
        $this->_file_view = new ciLogViewer_FileView($this);
    }

    public static function getPluginDirUrl()
    {
        if (!self::$_pluginDirUrl) {
            self::$_pluginDirUrl = plugin_dir_url(__FILE__);
        }
    }

    public static function transformFilePath($file)
    {
        $path = realpath(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $file);

        return $path;
    }

    public function getFiles()
    {
        if (empty($this->_files)) {
            $this->_updateFiles();
        }

        return $this->_files;
    }

    public function hasFiles()
    {
        $this->getFiles();
        if (empty($this->_files)) {
            return false;
        }

        return true;
    }

    private function _updateFiles()
    {
        $this->_files = array();

        $wp_c = realpath(WP_CONTENT_DIR);

        $str     = $wp_c . DIRECTORY_SEPARATOR . "*.log";
        $f       = glob($str);
        $str_rep = $wp_c . DIRECTORY_SEPARATOR;

        foreach ($f as $file) {
            $this->_files[] = str_replace($str_rep, "", $file);
        }
    }
}

/**
 *
 */
final class ciLogViewer_FileView
    extends CI_WP_AdminSubPage
{
    private $_plugin = null;
    private $_currentFile = false;
    private $_settings = array(
        'autorefresh' => 1,
        'display'     => 'fifo',
        'refreshtime' => 15,
    );

    public function __construct($plugin)
    {
        $this->_plugin = $plugin;

        parent::_initialize(
            __('Files View'),
            __('Log Viewer'),
            'ciLogViewer',
            'manage_options',
            self::SUBMENU_PARENTSLUG_TOOLS
        );
    }

    public function onViewPage()
    {
        global $action, $file, $file2, $display, $autorefresh, $Apply;
        wp_reset_vars(array('action', 'file', 'file2', 'display', 'autorefresh', 'Apply'));

        $this->_loadUserSettings();

        $file = $file2;

        $newSettings = $this->_settings;
        if ($Apply) {
            !$autorefresh ? $newSettings["autorefresh"] = 0 : $newSettings["autorefresh"] = 1;
            !$display ? $newSettings["display"] = $this->_settings["display"] : $newSettings["display"] = $display;
        }
        //var_dump($newSettings);echo"<br/>";
        //var_dump($this->_settings);echo"<br/>";
        if ($this->_settings["autorefresh"] === 1) {
            ?>
            <script type="text/javascript">
                setTimeout("window.location.replace(document.URL);", <?php echo $this->_settings["refreshtime"] * 1000 ?>);
            </script>
        <?php
        }
        if (is_user_logged_in()) {
            $this->_updateUserSettings($newSettings);
        }

        $this->_draw_header();

        if (!$this->_plugin->hasFiles()) {
            ?>
            <div id="message" class="updated">
                <p><?php _e('No files found.'); ?></p>
            </div>
            <?php
            return;
        }

        $files = $this->_plugin->getFiles();

        if (isset($_REQUEST['file']))
            $file = stripslashes($_REQUEST['file']);
        else
            $file = $files[0];

        $this->_currentFile = validate_file_to_edit($file, $this->_plugin->getFiles());
        $realfile           = ciLogViewer::transformFilePath($this->_currentFile);

        $writeable = is_writeable($realfile);

        // TODO: Scroll to like plugin-editor.php
        //$scrollto = isset($_REQUEST['scrollto']) ? (int) $_REQUEST['scrollto'] : 0;

        if (!$writeable) {
            $action = false;
            ?>
            <div id="message" class="updated">
                <p><?php _e('You can not edit file ( not writeable ).'); ?></p>
            </div>
        <?php
        }

        switch ($action) {
            case 'dump':
                $dumped = unlink($realfile);
                if ($dumped) :
                    ?>
                    <div id="message" class="updated">
                        <p><?php _e('File dumped successfully.'); ?></p>
                    </div>
                    <?php return; else :
                    ?>
                    <div id="message" class="error">
                        <p><?php _e('Could not dump file.'); ?></p>
                    </div>
                <?php
                endif;
                break;
            case 'empty':
                $handle = fopen($realfile, 'w');
                if (!$handle) :
                    ?>
                    <div id="message" class="error">
                        <p><?php _e('Could not open file.'); ?></p>
                    </div>
                <?php
                endif;

                $handle = fclose($handle);
                if (!$handle) :
                    ?>
                    <div id="message" class="error">
                        <p><?php _e('Could not empty file.'); ?></p>
                    </div>
                <?php else : ?>
                    <div id="message" class="updated">
                        <p><?php _e('File empty successfull.'); ?></p>
                    </div>
                <?php
                endif;

                break;
            case 'break':
                if (!error_log('------', 0)) :
                    ?>
                    <div id="message" class="error">
                        <p><?php _e('Could not update file.'); ?></p>
                    </div>
                <?php else : ?>
                    <div id="message" class="updated">
                        <p><?php _e('File updated successfully.'); ?></p>
                    </div>
                <?php
                endif;

                break;
            default:
                break;
        }
        ?>
        <div class="fileedit-sub">
            <strong>
                <?php printf('%1$s <strong>%2$s</strong>', __('Showing'), str_replace(realpath(ABSPATH), "", $realfile)) ?>
            </strong>

            <div class="tablenav top">

                <?php if ($writeable) : ?>

                    <div class="alignleft">
                        <form method="post" action="<?php echo $this->getPageUrl(); ?>">
                            <input type="hidden" value="<?php echo $this->_currentFile; ?>" name="file"/>
                            <input id="scrollto" type="hidden" value="0" name="scrollto">
                            <select name="action">
                                <option selected="selected" value="-1"><?php _e('File Actions'); ?></option>
                                <option value="dump"><?php _e('Dump'); ?></option>
                                <option value="empty"><?php _e('Empty'); ?></option>
                                <option value="break"><?php _e('Break'); ?></option>
                            </select>
                            <?php submit_button(__('Do'), 'button', 'Do', false); ?>
                        </form>
                    </div>

                <?php endif; ?>
                <div class="alignright">
                    <form method="post" action="<?php echo $this->getPageUrl(); ?>">
                        <input type="hidden" value="<?php echo $this->_currentFile; ?>" name="file2"/>
                        <input type="checkbox" value="1" <?php checked(1 == $this->_settings['autorefresh']); ?>
                               name="autorefresh"/>
                        <label for="autorefresh">Autorefresh</label>
                        <select name="display">
                            <option <?php selected('fifo' == $this->_settings['display']); ?> value="fifo">FIFO</option>
                            <option <?php selected('filo' == $this->_settings['display']); ?> value="filo">FILO</option>
                        </select>
                        <?php submit_button(__('Apply'), 'button', 'Apply', false); ?>
                    </form>
                </div>
            </div>

        </div>
        <div id="templateside">
            <h3>Log Files</h3>
            <ul>
                <?php foreach ($files as $file):
                    if ($this->_currentFile === $file) {
                        ?>
                        <li class="highlight">
                    <?php
                    } else {
                        ?>
                        <li>
                    <?php
                    }
                    ?>
                    <a href="<?php printf("%s&file=%s", $this->getPageUrl(), $file); ?>">
                        <?php echo $file; ?>
                    </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div id="template">
            <div>
                <?php if (!is_file($realfile)) : ?>
                    <div id="message" class="error">
                        <p><?php _e('Could not load file.'); ?></p>
                    </div>
                <?php else : ?>
                    <textarea id="newcontent" name="newcontent" rows="25" cols="70"
                              readonly="readonly"><?php echo $this->_getCurrentFileContent(); ?></textarea>
                <?php endif; ?>
                <div>
                    <h3><?php _e('Fileinfo'); ?></h3>
                    <dl>
                        <dt><?php _e('Fullpath:'); ?></dt>
                        <dd><?php echo $realfile; ?></dd>
                        <dt><?php _e('Last updated: '); ?></dt>
                        <dd><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), filemtime($realfile)); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <?php

        $this->_draw_footer();

    }

    private function _getCurrentFileContent()
    {
        if ($this->_settings["display"] == "filo") {
            $result = implode(array_reverse(file(ciLogViewer::transformFilePath($this->_currentFile))));
        } else {
            $result = file_get_contents(ciLogViewer::transformFilePath($this->_currentFile), false);
        }

        return $result;
    }

    private function _draw_header()
    {
        ?>
        <div class="wrap">
        <div id="icon-tools" class="icon32"><br/></div>
        <h2><?php echo $this->_page_title; ?></h2>
    <?php
    }

    private function _draw_footer()
    {
        ?>
        <br class="clear"/>
        </div>
    <?php
    }

    private function _loadUserSettings()
    {
        if (is_user_logged_in()) {
            $id         = wp_get_current_user();
            $id         = $id->ID;
            $optionskey = $id . "_log-viewer_settings";

            $settings = get_option($optionskey, false);
            if ($settings === false) {
                add_option($optionskey, $this->_settings);
            } elseif (!is_array($settings)) {
                update_option($optionskey, $this->_settings);
            } else {
                $this->_settings = $settings;
            }
        }
    }

    private function _updateUserSettings($settings)
    {
        if (is_user_logged_in()) {
            $id         = wp_get_current_user();
            $id         = $id->ID;
            $optionskey = $id . "_log-viewer_settings";
            if ($settings != $this->_settings) {
                update_option($optionskey, $settings);
                $this->_settings = $settings;
                //echo 'Update!!'; var_dump($settings);
            } else {
                //var_dump($settings);echo '<br/>';
                //var_dump($this->_settings);echo '<br/>';
                //echo 'Nix Upddate!!';
            }
        }
    }
}
