<?php

namespace Drupal\girchi_front\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class GirchiFrontController.
 */
class GirchiFrontController extends ControllerBase {

  /**
   * Index.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {
    return [
      '#type' => 'markup',
      '#theme' => 'girchi_front'
    ];
  }

}
