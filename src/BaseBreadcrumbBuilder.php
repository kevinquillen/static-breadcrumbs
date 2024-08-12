<?php

declare(strict_types=1);

namespace Drupal\static_breadcrumbs;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Provide a base class for builders to use.
 *
 * @package Drupal\static_breadcrumbs
 */
abstract class BaseBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The AdminContext service.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * The AliasManager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    AdminContext $admin_context,
    AliasManagerInterface $alias_manager,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
  ) {
    $this->adminContext = $admin_context;
    $this->aliasManager = $alias_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->config = $config_factory->get('static_breadcrumbs.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) : bool {
    if ($this->adminContext->isAdminRoute()) {
      return FALSE;
    }

    $node = $route_match->getParameter('node');
    return (isset($node) && $node instanceof NodeInterface);
  }

  /**
   * Truncate a long string for the breadcrumb.
   *
   * @param string $title
   *   The original string.
   *
   * @return string
   *   The truncated string.
   */
  protected function truncateTitle(string $title) : string {
    return Unicode::truncate($title, 40, TRUE, TRUE);
  }

}
