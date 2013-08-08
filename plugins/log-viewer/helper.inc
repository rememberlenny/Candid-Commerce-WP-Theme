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

abstract class CI_WP_AdminSubPage
{
    const SUBMENU_PARENTSLUG_DASHBOARD  = 'index.php';
    const SUBMENU_PARENTSLUG_POSTS      = 'edit.php';
    const SUBMENU_PARENTSLUG_MEDIA      = 'upload.php';
    const SUBMENU_PARENTSLUG_LINKS      = 'link-manager.php';
    const SUBMENU_PARENTSLUG_PAGES      = 'edit.php?post_type=page';
    const SUBMENU_PARENTSLUG_COMMENTS   = 'edit-comments.php';
    const SUBMENU_PARENTSLUG_APPEARANCE = 'themes.php';
    const SUBMENU_PARENTSLUG_PLUGINS    = 'plugins.php';
    const SUBMENU_PARENTSLUG_USERS      = 'users.php';
    const SUBMENU_PARENTSLUG_TOOLS      = 'tools.php';
    const SUBMENU_PARENTSLUG_SETTINGS   = 'options-general.php';

    protected $_parent = self::SUBMENU_PARENTSLUG_TOOLS;
    protected $_page_title;
    protected $_capability;
    protected $_menu_title;
    protected $_menu_slug;
    protected $_hook_suffix;

    /**
     *
     * @var WP_Screen
     */
    protected $_wpScreen;

    public function getWPScreen()
    {
        if (!$this->_wpScreen) {
            $this->_wpScreen = WP_Screen::get($this->_hook_suffix);
        }

        return $this->_wpScreen;
    }

    public function _initialize($page_title, $menu_title, $menu_slug, $capability = 'manage_options', $parent = self::SUBMENU_PARENTSLUG_TOOLS)
    {
        $this->_page_title = $page_title;
        $this->_menu_title = $menu_title;
        $this->_menu_slug  = $menu_slug;
        $this->_capability = $capability;
        $this->_parent     = $parent;

        add_action('admin_menu', array(&$this, 'onAddSubPage'));
    }

    public function onAddSubPage()
    {
        $this->_hook_suffix = add_submenu_page(
            $this->_parent,
            $this->_page_title,
            $this->_menu_title,
            $this->_capability,
            $this->_menu_slug,
            array(&$this, 'onViewPage')
        );

        return $this->_hook_suffix;
    }

    public function getPageUrl()
    {
        $url = admin_url($this->_parent, 'admin');
        $url .= "?page=" . $this->_menu_slug;

        return $url;
    }

    public abstract function onViewPage();
}