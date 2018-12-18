<?php

namespace Metadrop\Behat\Cores;

use NuvoleWeb\Drupal\Driver\Cores\Drupal8 as OriginalDrupal8;
use Metadrop\Behat\Cores\Traits\UsersTrait;
use Metadrop\Behat\Cores\Traits\CronTrait;
use Metadrop\Behat\Cores\Traits\FileTrait;
use Webmozart\Assert\Assert;
use Behat\Behat\Tester\Exception\PendingException;
use Drupal\user\Entity\User;
use Drupal\paragraphs\Entity\Paragraph;

class Drupal8 extends OriginalDrupal8 implements CoreInterface {

  use UsersTrait;
  use CronTrait;
  use FileTrait;

  /**
   * {@inheritdoc}
   */
  public function cacheClear($cid, $bin = 'cache') {
    \Drupal::cache($bin)->delete($cid);
  }

  /**
   * {@inheritdoc}
   */
  public function viewsCacheClear($view_name) {
    throw new PendingException('Views cache clearing not implemented yet in Drupal 8!');
  }

  /**
   * {@inheritdoc}
   */
  public function runElysiaCron() {
    throw new PendingException('Elysia cron run not implemented yet!');
  }

  /**
   * {@inheritdoc}
   */
  public function runElysiaCronJob($job) {
    throw new PendingException('Elysia job cron run not implemented yet!');
  }

  /**
   * {@inheritdoc}
   */
  public function loadUserByProperty($property, $value, $reset = TRUE) {
    $query = \Drupal::entityQuery('user');
    $query->condition($property, $value);
    $entity_ids = $query->execute();
    return !empty($entity_ids) ? User::load(reset($entity_ids)) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserRoles($user) {
    return $user->getRoles();
  }

  /**
   * Discovers last entity id created of type.
   */
  public function getLastEntityId($entity_type, $bundle = NULL) {

    $info = \Drupal::entityTypeManager()->getDefinition($entity_type);
    $id_key = $info->getKey('id');
    $bundle_key = $info->getKey('bundle');

    $query = \Drupal::entityQuery($entity_type);
    if ($bundle) {
      $query->condition($bundle_key, $bundle);
    }
    $query->sort($id_key, 'DESC');
    $query->range(0, 1);
    $query->addMetaData('account', user_load(1));
    $results = $query->execute();

    if (!empty($results)) {
      $id = reset($results);
      return $id;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function entityUri($entity_type, $entity) {
    return $entity->toUrl()->getInternalPath();
  }

  /**
   * {@inheritdoc}
   */
  public function entityLoadSingle($entity_type, $id) {
    $controller = \Drupal::entityManager()->getStorage($entity_type);
    $entity = $controller->load($id);
    Assert::notEq($entity, FALSE, 'Entity of type "' . $entity_type . '" with id "' . $id . '" does not exists.');
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function attachParagraphToEntity($paragraph_type, $paragraph_field, array $paragraph_values, $entity, $entity_type) {
    $paragraph_values['type'] = $paragraph_type;
    $paragraph = Paragraph::create($paragraph_values);
    $paragraph->save();
    $entity->get($paragraph_field)->appendItem($paragraph);
  }

  /**
   * {@inheritdoc}
   */
  public function entityId($entity_type, $entity) {
    return $entity->id();
  }

  /**
   * {@inheritdoc}
   */
  public function entitySave($entity_type, $entity) {
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function nodeAccessAcquireGrants($node) {
    throw new PendingException('Node access grants not implemented yet!');
  }

}
