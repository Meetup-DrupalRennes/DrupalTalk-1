<?php

namespace Drupal\drupalprez_one\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface as ContainerFactoryPluginInterfaceAlias;
use Drupal\Core\Session\AccountProxy;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a cached block.
 *
 * @Block(
 *  id = "cached_block",
 *  admin_label = @Translation("Bloc avec cache de base"),
 * )
 */
class CachedBlock extends BlockBase implements ContainerFactoryPluginInterfaceAlias {

  private $entityTypeManager;

  private $currentUser;

  private $latestPost;

  /**
   * NoCacheBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Session\AccountProxy $current_user
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AccountProxy $current_user,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser =
      $entity_type_manager->getStorage('user')
        ->load($current_user->id());
    $this->latestPost = $this->getLatestPost();
  }

  /**
   * Returns latest post title.
   *
   * @return string|null
   *   The post title.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getLatestPost() {
    $storage = $this->entityTypeManager->getStorage('node');
    $posts =
      $storage->getQuery()
        ->condition('uid', $this->currentUser->id())
        ->execute();

    if (empty($posts)) {
      return NULL;
    }
    $post = array_pop($posts);
    $post = $storage->load($post);
    return $post->getTitle();
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('current_user'), $container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'drupalprez_theme',
      '#username' => $this->currentUser->getAccountName(),
      '#latest_post' => !empty($this->latestPost) ? $this->latestPost : NULL,
    ];
  }

}
