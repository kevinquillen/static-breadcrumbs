<?php

namespace Drupal\static_breadcrumbs;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\system\PathBasedBreadcrumbBuilder;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * Defines a class to build path-based breadcrumbs.
 *
 * @see \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface
 */
class PathBasedBreadcrumbBuilderOverride extends PathBasedBreadcrumbBuilder {

  /**
   * The admin context service.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestContext $context, AccessManagerInterface $access_manager, RequestMatcherInterface $router, InboundPathProcessorInterface $path_processor, ConfigFactoryInterface $config_factory, TitleResolverInterface $title_resolver, AccountInterface $current_user, CurrentPathStack $current_path, PathMatcherInterface $path_matcher = NULL, AdminContext $admin_context = NULL) {
    parent::__construct($context, $access_manager, $router, $path_processor, $config_factory, $title_resolver, $current_user, $current_path, $path_matcher);
    $this->adminContext = $admin_context;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return TRUE;
  }

}
