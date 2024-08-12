<?php

declare(strict_types=1);

namespace Drupal\static_breadcrumbs;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * A basic breadcrumb builder for nodes.
 *
 * @package Drupal\static_breadcrumbs
 */
class GenericBreadcrumbBuilder extends BaseBreadcrumbBuilder {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) : bool {
    return (parent::applies($route_match) && in_array($route_match->getParameter('node')->getType(), $this->approvedTypes()));
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) : Breadcrumb {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $route_match->getParameter('node');
    $node_type = $this->entityTypeManager->getStorage('node_type')->load($node->getType());
    $crumbs = $node_type->getThirdPartySetting('static_breadcrumbs', 'breadcrumb_path') ?? [];
    $breadcrumb = new Breadcrumb();

    $langcode = $node->language()->getId();
    $home = $this->t('Home', [], ['langcode' => $langcode]);
    $breadcrumb->addLink(Link::fromTextAndUrl($home, Url::fromUri('base:/')));

    foreach ($crumbs as $crumb) {
      $item = $this->entityTypeManager->getStorage('node')->load($crumb);

      if (isset($item) && $item instanceof NodeInterface) {
        $title = $this->getTitle($item);
        $breadcrumb->addLink(Link::fromTextAndUrl($title, Url::fromRoute('entity.node.canonical', ['node' => $item->id()])));
        $breadcrumb->addCacheableDependency($item);
      }
    }

    $breadcrumb->addLink(Link::fromTextAndUrl($node->label(), Url::fromUri('internal:/')));
    $breadcrumb->addCacheContexts(['url.path']);
    $breadcrumb->addCacheableDependency($node);
    $breadcrumb->addCacheableDependency($node_type);
    $breadcrumb->addCacheableDependency($this->config);
    return $breadcrumb;
  }

  /**
   * Return a list of approved content types that apply for this breadcrumb.
   *
   * @return array
   *   Approved bundles from the admin form.
   */
  private function approvedTypes() : array {
    $types = $this->config->get('allowed_types') ?? [];
    return array_keys($types);
  }

  /**
   * Return the title or the short title if available.
   */
  private function getTitle(ContentEntityBase $entity) {
    $title = $entity->label();

    if ($entity->hasField('field_short_title')) {
      $short_title = $entity->field_short_title->value;

      if (!empty($short_title)) {
        $title = $short_title;
      }
    }

    return $title;
  }

}
