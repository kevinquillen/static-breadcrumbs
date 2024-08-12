<?php

namespace Drupal\Tests\static_breadcrumbs\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\system\Functional\Menu\AssertBreadcrumbTrait;

/**
 * Tests breadcrumbs functionality.
 *
 * @group static_breadcrumbs
 * @package Drupal\Tests\static_breadcrumbs\Functional
 */
class BreadcrumbTest extends BrowserTestBase {

  use AssertBreadcrumbTrait, StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'system', 'static_breadcrumbs', 'block'];

  /**
   * The default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'stable9';

  /**
   * An administrative user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A regular user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * A node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node1;

  /**
   * A node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node2;

  /**
   * A node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node3;

  /**
   * A node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node4;

  /**
   * A node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node5;

  /**
   * A node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node6;

  /**
   * Test paths in the testing profile.
   *
   * @var string
   */
  protected $profile = 'testing';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $perms = array_keys(\Drupal::service('user.permissions')->getPermissions());
    $this->adminUser = $this->drupalCreateUser($perms);
    $this->drupalLogin($this->adminUser);
    $this->drupalCreateContentType(['type' => 'page']);

    FieldStorageConfig::create([
      'field_name' => 'field_short_title',
      'entity_type' => 'node',
      'type' => 'text',
      'cardinality' => 1,
      'locked' => FALSE,
      'indexes' => [],
      'settings' => [
        'max_length' => 255,
        'is_ascii' => FALSE,
        'case_sensitive' => FALSE,
      ],
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_short_title',
      'entity_type' => 'node',
      'label' => 'Short Title',
      'bundle' => 'page',
      'description' => '',
      'required' => FALSE,
      'settings' => [],
    ])->save();

    $this->node1 = $this->drupalCreateNode(
      [
        'title' => 'Page One',
        'type' => 'page',
      ]
    );

    $this->node2 = $this->drupalCreateNode(
      [
        'title' => 'Page Two',
        'type' => 'page',
      ]
    );

    $this->node3 = $this->drupalCreateNode(
      [
        'title' => 'Page Three',
        'type' => 'page',
      ]
    );

    $this->node4 = $this->drupalCreateNode(
      [
        'title' => 'Page Four',
        'type' => 'page',
      ]
    );

    $this->node5 = $this->drupalCreateNode([
      'title' => 'Title Should Be Truncated to Forty Characters',
      'type' => 'page',
    ]);

    $this->node6 = $this->drupalCreateNode([
      'title' => 'Page Six',
      'type' => 'page',
      'field_short_title' => 'Short Title',
    ]);

    $this->drupalPlaceBlock('system_breadcrumb_block', ['region' => 'content']);
  }

  /**
   * Test different configuration of breadcrumbs on content type(s).
   */
  public function testBreadcrumbs() {
    // The option should not show until we have allowed it to show.
    $this->drupalGet('/admin/structure/types/manage/page');
    $this->assertSession()->elementNotExists('css', 'details#edit-breadcrumb');

    // Set an option.
    $this->drupalGet('/admin/config/system/breadcrumb-settings');

    $edit = [
      'edit-allowed-types-page' => 'page',
      'edit-bundles-page' => 'page',
    ];
    $this->submitForm($edit, 'Save configuration');

    $this->drupalGet('/admin/structure/types/manage/page');
    $this->assertSession()->elementExists('css', 'details#edit-breadcrumb');

    // Add 2 breadcrumbs.
    $this->click('#edit-breadcrumb-add');
    $this->click('#edit-breadcrumb-add');

    $edit = [
      'edit-breadcrumb-items-breadcrumb-path-0' => 'Page One (' . $this->node1->id() . ')',
      'edit-breadcrumb-items-breadcrumb-path-1' => 'Page Two (' . $this->node2->id() . ')',
      'edit-breadcrumb-items-breadcrumb-path-2' => 'Page Three (' . $this->node3->id() . ')',
    ];
    $this->submitForm($edit, 'Save');

    $trail = [
      '/' => 'Home',
      '/node/' . $this->node1->id() => 'Page One',
      '/node/' . $this->node2->id() => 'Page Two',
      '/node/' . $this->node3->id() => 'Page Three',
      '' => 'Page Four',
    ];

    $this->assertBreadcrumb('/node/' . $this->node4->id(), $trail);
  }

}
