<?php

namespace Drupal\panoptic_connector\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Controller\ControllerBase;

class PCController extends ControllerBase {

  public function __construct(){
    $iv = base64_decode('b616An+SM3eyXJV3FJzluIsC7X110gIjup9gY1JxNN8=');
    define('PANOPTIC_CONNECTOR_IV', $iv);
  }

  public function content() {
    $update_data = $this->connect();
    $build = array(
      '#type' => 'markup',
      '#markup' => $update_data,
    );
    $Response = new Response;
    $Response->setContent(render($build));
    $Response->headers->set('Content-Type', 'text/plain');
    return $Response;
  }

  function connect() {
    $key = \Drupal::config('panoptic_connector.settings')->get('panoptic_connector.vcode');
    if (empty($key)) {
      drupal_set_message('VCode not found');
      return;
    }
    module_load_include('inc', 'update', 'update.compare');
    $data = array();
    $data['server-time'] = time();
    $available = update_get_available();
    $update_data = update_calculate_project_data($available);
    $needs_update = array_filter($update_data, array($this, '_filter_project_status'));
    $data['updates'] = $needs_update;
    $data = json_encode($data);
    $enc_str = $this->_panoptic_connector_encrypt($key, $data);
    return $enc_str;
  }

  function _filter_project_status($project_info) {
    $status = $project_info['status'];
    $status_ignore = array(
      5,  // Module is up to date.
      -3, // Failed to get available update data.
      -4, // No available update data,
      -2, // No available releases found
    );
    return !in_array($status, $status_ignore, TRUE);
  }

  /**
  * Encryption for PHP7.1+
  *
  * @param $key
  * @param $str
  * @return string
  */
  function _panoptic_connector_encrypt($key, $str) {
    $cipher = "aes-128-gcm";
    if (in_array($cipher, openssl_get_cipher_methods())) {
      $iv  = PANOPTIC_CONNECTOR_IV;
      $enc = openssl_encrypt($str, $cipher, $key, 0, $iv, $tag);
    }
    return trim(base64_encode($enc . '||||' . $tag));
  }

}

?>