<?php

class Redirect_IndexController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $db = get_db();
        $sql = "SELECT * FROM {$db->prefix}redirect";

        $this->view->redirects = $db->query($sql);
        $this->view->head = array(
            'title' => __('Redirect')
        );
    }

    public function addAction() {
        $id = $this->_getParam('id');
        $db = get_db();
        $this->view->head = array('title' => __('Add Redirect'));
        $this->view->element = (object) array(
            'source' => '',
            'redirect' => '',
            'enabled' => 1,
            'id' => 0,
        );

        $flashMessenger = $this->_helper->FlashMessenger;
        include_once REDIRECT_DIR . '/forms/edit-form.php';
        try {
            $this->view->form = new Redirect_EditForm();
            $this->view->form->setElement($this->view->element);
            $this->view->form->create();
        }
        catch(Exception $e) {
            $flashMessenger->addMessage('Error rendering edit form ' . $e, 'error');
        }
    }

    public function editAction() {
        $id = $this->_getParam('id');
        $db = get_db();

        $this->view->head = array('title' => __('Edit Redirect'));
        $this->view->element = $this->_getElement($id);

        $flashMessenger = $this->_helper->FlashMessenger;
        include_once REDIRECT_DIR . '/forms/edit-form.php';
        try {
            $this->view->form = new Redirect_EditForm();
            $this->view->form->setElement($this->view->element);
            $this->view->form->create();
        }
        catch(Exception $e) {
            $flashMessenger->addMessage('Error rendering edit form ' . $e, 'error');
        }
    }

    public function saveAction()
    {
        $db = get_db();
        $params = $this->_getAllParams();
        $id = $params['id'];

        $enabled = $params['enabled'];
        $trimmed_source = trim($params['source'], '/');
        $source = '/' . $trimmed_source;

        // set where to redirect the user should something fail
        // if $id is empty, we're inserting a new redirect, so send them to the redirect add
        if (empty($id)) {
            $redirect = 'redirect/index/add';
        }
        // else we're editing an existing redirect, so send them there
        else {
            $redirect = 'redirect/index/edit/' . $id;
        }

        // add the user-supplied values as $_GET parameters
        $redirect .= '?source='
                . $params['source']
                . '&redirect='
                . $params['redirect']
                . '&enabled=' . $params['enabled'];

        // don't let people redirect admin URLS. Could get into trouble that way
        if ($source == '/admin' || strpos($source, '/admin/') !== FALSE) {
            $flashMessenger = $this->_helper->FlashMessenger;
            $flashMessenger->addMessage(__('You can not add redirects for admin pages.'),
            'error');
            $this->_redirect($redirect . '&error=source');
            return;
        }

        // make sure the redirect doesn't already exist
        if ($_redirect = $db->query("SELECT id FROM `{$db->prefix}redirect` WHERE source = ? AND id <> ?", array($trimmed_source, $id))->fetchObject()) {
            $flashMessenger = $this->_helper->FlashMessenger;
            $flashMessenger->addMessage('This redirect already exists. You can edit the existing redirect at <a href="' . absolute_url('redirect/index/edit/id/' . $_redirect->id).'">'
                .absolute_url('redirect/index/edit/id/' . $_redirect->id).'</a>.',
            'error');

            $this->_redirect($redirect . '&error=source');
            return;
        }
/*
        // see if the URL being redirected has a 200 HTTP response
        // @todo perhaps Zend has a way to query routes? I'm sure it does but couldn't find after a quick google search
        // @todo allow override by admin
        $serverUrlHelper = new Zend_View_Helper_ServerUrl;
        $full_url = $serverUrlHelper->serverUrl() . public_url($params['source']);
        $client = new Zend_Http_Client($full_url);
        $response = $client->request('HEAD');
        if ($response->getStatus() == 200) {
            $flashMessenger = $this->_helper->FlashMessenger;
            $flashMessenger->addMessage(__('You can\'t add a redirect for an existing page.'),
            'error');
            $this->_redirect($redirect . '&error=redirect');
            return;
        }

        // see if the URL being redirected to is valid
        if (filter_var($params['redirect'], FILTER_VALIDATE_URL)) {
            $full_url = $params['redirect'];
        }
        else {
            $full_url = $serverUrlHelper->serverUrl() . public_url($params['redirect']);
        }
        $client = new Zend_Http_Client($full_url);
        $response = $client->request('HEAD');
        if ($response->getStatus() != 200) {
            $flashMessenger = $this->_helper->FlashMessenger;
            $flashMessenger->addMessage(__("The URL you're redirecting to doesn't exist."),
            'error');
            $this->_redirect($redirect . '&error=redirect');
            return;
        }
*/
        // if the user is deleting the redirect, just delete it from the database
        if (!empty($params['delete'])) {
            $db->query("DELETE FROM `{$db->prefix}redirect` WHERE id = ?", $id);
            $msg = 'Successfully deleted redirect.';
        }
        // else the user is adding/updating a redirect
        else {
            $keys = array(
                'action',
                'admin',
                'delete',
                'controller',
                'id',
                'module',
            );
            foreach ($params as $key => $value) {
                if (in_array($key, $keys)) {
                    unset($params[$key]);
                }
                else {
                    $params[$key] = trim($value);
                }
            }
            foreach ($keys as $key) {
                unset($params[$key]);
            }

            // if inserting a new redirect
            if (empty($id)) {
                $insert_sql = "INSERT INTO `{$db->prefix}redirect`(`"
                    . implode('`, `', array_keys($params))
                    . "`) VALUES (";
                foreach ($params as $key => $value) {
                    $insert_sql .= '?,';
                }

                $insert_sql = trim($insert_sql, ',') . ')';

                $db->query($insert_sql, $params);
                $msg = 'Successfully added redirect.';
            }
            // else updating an existing redirect
            else {
                $update_sql = "UPDATE `{$db->prefix}redirect` SET `"
                    . implode('` = ?, `', array_keys($params))
                    . "` = ? WHERE id = $id";

                $db->query($update_sql, $params);
                $msg = 'Successfully updated redirect.';
            }
        }
        $this->_helper->flashMessenger(__($msg),
            'success');

        $this->_helper->redirector('index', 'index');
    }

    protected function _getElement($id) {
        $db = get_db();
        return $db->query("SELECT * FROM `{$db->prefix}redirect` WHERE id = ?", $id)->fetchObject();
    }
}
