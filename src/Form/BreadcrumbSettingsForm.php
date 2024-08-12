<?php

namespace Drupal\static_breadcrumbs\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Breadcrumbs settings for this site.
 */
class BreadcrumbSettingsForm extends ConfigFormBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * BreadcrumbSettingsForm constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager) {
    $this->setConfigFactory($config_factory);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'static_breadcrumbs_breadcrumb_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['static_breadcrumbs.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $options = [];

    $types = $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();

    foreach ($types as $type) {
      $options[$type->id()] = $type->label();
    }

    $form['allowed_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types to use static breadcrumbs on'),
      '#options' => $options,
      '#description' => $this->t('Select which content types should have a static breadcrumb set.'),
      '#default_value' => $this->config('static_breadcrumbs.settings')->get('allowed_types') ?? [],
    ];

    $form['bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types allowed to be referenced'),
      '#options' => $options,
      '#description' => $this->t('Select which content types are allowed to be referenced in the breadcrumb settings for a content type.'),
      '#default_value' => $this->config('static_breadcrumbs.settings')->get('node_bundles') ?? [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $allowed_types = $form_state->getValue('allowed_types');
    $allowed_types = array_filter($allowed_types);

    $bundles = $form_state->getValue('bundles');
    $bundles = array_filter($bundles);

    $this->config('static_breadcrumbs.settings')
      ->set('node_bundles', $bundles)
      ->set('allowed_types', $allowed_types)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
