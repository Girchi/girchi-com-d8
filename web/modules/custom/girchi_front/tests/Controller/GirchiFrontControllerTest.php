<?php

namespace Drupal\girchi_front\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the girchi_front module.
 */
class GirchiFrontControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "girchi_front GirchiFrontController's controller functionality",
      'description' => 'Test Unit for module girchi_front and controller GirchiFrontController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests girchi_front functionality.
   */
  public function testGirchiFrontController() {
    // Check that the basic functions of module girchi_front.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
