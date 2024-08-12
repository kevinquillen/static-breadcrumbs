<?php

declare(strict_types=1);

namespace Drupal\static_breadcrumbs;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManagerInterface;

/**
 * A basic breadcrumb builder for users.
 *
 * @package Drupal\static_breadcrumbs
 */
final class UserBreadcrumbBuilder implements BreadcrumbBuilderInterface {

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
   * {@inheritdoc}
   */
  public function __construct(
    AdminContext $admin_context,
    AliasManagerInterface $alias_manager,
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    $this->adminContext = $admin_context;
    $this->aliasManager = $alias_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) : bool {
    if ($this->adminContext->isAdminRoute()) {
      return FALSE;
    }

    $account = $route_match->getParameter('user');
    return (isset($account) && $account instanceof AccountInterface);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) : Breadcrumb {
    /** @var \Drupal\user\Entity\User $account */
    $account = $route_match->getParameter('user');
    $roles = $account->getRoles(TRUE);
    $skip_roles = ['administrator', 'content_author', 'content_publisher'];
    $crumbs = [];

    foreach ($roles as $id) {
      $role = $this->entityTypeManager->getStorage('user_role')->load($id);

      // Use the first valid role we find.
      if (!in_array($role->id(), $skip_roles)) {
        $crumbs = $role->getThirdPartySetting('static_breadcrumbs', 'breadcrumb_path');
        break;
      }
    }

    $breadcrumb = new Breadcrumb();
    $langcode = $account->language()->getId();
    $home = $this->t('Home', [], ['langcode' => $langcode]);
    $breadcrumb->addLink(Link::fromTextAndUrl($home, Url::fromUri('base:/')));

    if (!empty($crumbs)) {
      foreach ($crumbs as $crumb) {
        $item = $this->entityTypeManager->getStorage('node')->load($crumb);

        if (isset($item) && $item instanceof NodeInterface) {
          $breadcrumb->addLink(Link::fromTextAndUrl($item->label(), Url::fromRoute('entity.node.canonical', ['node' => $item->id()])));
          $breadcrumb->addCacheableDependency($item);
        }
      }
    }

    $breadcrumb->addCacheContexts(['url.path']);
    $breadcrumb->addCacheContexts(['languages:language_content']);
    $breadcrumb->addCacheableDependency($account);

    if (isset($role)) {
      $breadcrumb->addCacheableDependency($role);
    }

    return $breadcrumb;
  }

}
