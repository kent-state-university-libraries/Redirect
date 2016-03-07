<?php

if (!defined('REDIRECT_DIR')) {
  define('REDIRECT_DIR', dirname(__FILE__));
}

require_once 'functions.php';

class RedirectPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'initialize',
        'install',
        'uninstall',
        'define_acl',
    );
    protected $_filters = array('admin_navigation_main', 'public_navigation_admin_bar');

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Create the table.
        $db = $this->_db;

        $sql = "CREATE TABLE `{$db->prefix}redirect` (
          `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Unique redirect ID.',
          `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The users.uid of the user who created the redirect.',
          `source` varchar(255) NOT NULL COMMENT 'The source path to redirect from.',
          `redirect` varchar(255) NOT NULL COMMENT 'The destination path to redirect to.',
          `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The number of times the redirect has been used.',
          `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The timestamp of when the redirect was last accessed.',
          `enabled` BOOLEAN NOT NULL DEFAULT '1' COMMENT 'Boolean indicating whether the redirect is enabled (visible to non-administrators).',
          PRIMARY KEY (`id`),
          UNIQUE KEY `source` (`source`)
        ) COMMENT='Stores information on redirects.';";
        $db->query($sql);

        $this->_installOptions();
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        // Drop the table.
        $db = $this->_db;
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}redirect`";
        $db->query($sql);

        $this->_uninstallOptions();
    }

    public function hookInitialize() {
        $db = get_db();
        $source = trim($_SERVER['REQUEST_URI'], '/');
        $d_args = array(
          $source,
          trim(str_replace(trim(public_url(''), '/'), '', $source), '/'),
        );
        $redirect = $db->query("SELECT id, redirect FROM `{$db->prefix}redirect`
            WHERE (source = ? OR source = ?) AND enabled = 1", $d_args)->fetchObject();
        if ($redirect) {
            $db->query("UPDATE `{$db->prefix}redirect` SET count = count + 1, access = ? WHERE id = ?", array(time(), $redirect->id));
            header('Location: /' . $redirect->redirect, TRUE, 301);
            exit;
        }

        if (!is_null(current_user())) {
            queue_css_string('a.redirect {
                padding-left: 15px !important;
                background: transparent url('.url('').'plugins/Redirect/views/shared/img/add.png) no-repeat 0 center;
                line-height: 30px;
            }');
        }
    }

    public function hookDefineAcl($args)
    {
        // Restrict access to super and admin users.
        $args['acl']->addResource('Redirect_Index');
    }

    public function filterAdminNavigationMain($nav) {
        $nav[] = array(
            'label' => __('Redirects'),
            'uri' => url('redirect'),
            'resource' => 'Redirect_Index',
            'privilege' => 'index'
        );
        return $nav;
    }

    public function filterPublicNavigationAdminBar($navLinks)
    {
        $view = get_view();
        if (isset($view->item)) {
            $record = $view->item;
            $aclRecord = $view->item;
        }

        if (isset($view->collection)) {
            $record = $view->collection;
            $aclRecord = $view->collection;
        }

        if (isset($view->simple_pages_page)) {
            $record = $view->simple_pages_page;
            $aclRecord = 'SimplePages_Page';
        }

        if (isset($view->exhibit_page)) {
            $record = $view->exhibit_page;
            $aclRecord = $view->exhibit;
        }

        if (!isset($record)) {
            return $navLinks;
        }

        if (is_allowed($aclRecord, 'edit')) {
            $editLinks = array(
                'redirect' => array(
                    'label'=>'Add Redirect',
                    'uri' => admin_url('redirect/index/add', array(
                        'redirect' => array_pop(explode('?', trim($_SERVER['REQUEST_URI'], '/')))
                    )),
                    'class' => 'redirect',
                    'target' => '_blank',
                ),
            );
        }

        $navLinks = array_merge($editLinks, $navLinks);

        return $navLinks;
    }
}
