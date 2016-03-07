<?php

class Redirect_EditForm extends Omeka_Form
{
  private $_element = NULL;

  public function init()
  {
      parent::init();
      $this->setMethod('post');
      $this->setAction(url('redirect/index/save'));
  }

  public function setElement($element) {
      $this->_element = $element;
  }

  public function create() {
      try {
          $this->_registerElements();
      }
      catch (Exception $e) {
        throw $e;
      }
  }

  private function _registerElements()
  {
      $error = isset($_GET['error']) ? $_GET['error'] : FALSE;

      // hidden ID element
      $id = $this->createElement('hidden', 'id');
      $id->setValue($this->_element->id);

      // hidden "delete" element
      // toggled by "Delete" button
      $del = $this->createElement('hidden', 'delete');
      $del->setValue(0);

      // source textfield
      $source = $this->createElement('text', 'source');
      $source->setLabel('Source');
      if (isset($_GET['source'])) {
        $source->setValue($_GET['source']);
      }
      else {
          $source->setValue($this->_element->source);
      }
      if ($error == 'source') {
        $source->setAttrib('class', 'error');
      }
      $source->setDescription('If someone visits this path they will be redirected to the URL in "Redirect".');

      // redirect textfield
      $redirect = $this->createElement('text', 'redirect');
      $redirect->setLabel('Redirect');
      if (isset($_GET['redirect'])) {
        $redirect->setValue($_GET['redirect']);
      }
      else {
          $redirect->setValue($this->_element->redirect);
      }
      if ($error == 'redirect') {
        $redirect->setAttrib('class', 'error');
      }
      $redirect->setDescription('If someone visits the "source" URL they will be redirected here.');

      // enabled checkbox
      $enabled = $this->createElement('checkbox', 'enabled');
      $enabled->setLabel('Enabled');
      if (isset($_GET['enabled'])) {
        $enabled->setValue($_GET['enabled']);
      }
      else {
          $enabled->setValue($this->_element->enabled);
      }
      $enabled->setDescription("Enable the redirect.");

      // add all the elements to the form
      $_elements = array(
          'id',
          'del',
          'source',
          'redirect',
          'enabled',
      );
      $elements = array();
      foreach ($_elements as $element) {
          $elements[] = $$element;
      }
      $this->addElements($elements);
  }
}
