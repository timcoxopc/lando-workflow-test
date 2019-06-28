<?php

namespace Drupal\commerce_migrate_commerce\Plugin\migrate\source\commerce1;

use CommerceGuys\Intl\Currency\CurrencyRepository;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Gets Commerce 1 commerce_line_items from source database.
 *
 * @MigrateSource(
 *   id = "commerce1_line_item",
 *   source_module = "commerce_order"
 * )
 */
class LineItem extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('commerce_line_item', 'li')
      ->fields('li');

    if (isset($this->configuration['line_item_type'])) {
      $query->condition('li.type', $this->configuration['line_item_type']);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'line_item_id' => t('Line Item ID'),
      'title' => t('Product title'),
      'order_id' => t('Order ID'),
      'type' => t('Type'),
      'line_item_label' => t('Line Item Label'),
      'quantity' => t('Quantity'),
      'created' => t('Created'),
      'changed' => t('Changes'),
      'data' => t('Data'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Get all the unique discounts for this order that are in
    // commerce_line_item. Then for each discount unserialize the data blob
    // (just like all fields) and add the percentage, if a percentage discount.
    $query = $this->select('commerce_line_item', 'li')
      ->fields('li', ['data'])
      ->fields('fdct', [
        'commerce_total_amount',
        'commerce_total_currency_code',
        'commerce_total_data',
      ])
      ->distinct()
      ->condition('order_id', $row->getSourceProperty('order_id'))
      ->condition('type', 'commerce_discount');
    $query->leftJoin('field_data_commerce_total', 'fdct', 'li.line_item_id = fdct.entity_id');
    $results = $query->execute()->fetchAll();

    if ($results) {
      $discounts = [];
      foreach ($results as $commerce_line_item) {
        // Set some defaults.
        $percentage = NULL;
        $data = NULL;
        if (isset($commerce_line_item['data'])) {
          // Get the data to find the name of the discount.
          $data = unserialize($commerce_line_item['data']);
          if ($data && !empty($data['discount_name'])) {
            // Use the discount_name to get the percentage value.
            $query = $this->select('commerce_discount', 'cd')
              ->fields('fdcp', ['commerce_percentage_value'])
              ->condition('cd.name', $data['discount_name']);
            $query->innerJoin('field_data_commerce_discount_offer', 'fdcdo', 'fdcdo.entity_id = cd.discount_id');
            $query->innerJoin('field_data_commerce_percentage', 'fdcp', 'fdcp.entity_id = fdcdo.commerce_discount_offer_target_id');
            $percentage = $query->execute()->fetchField();
          }
        }
        // Build a discount array similar to a price component.
        $commerce_line_item['amount'] = $commerce_line_item['commerce_total_amount'];
        unset($commerce_line_item['commerce_total_amount']);
        $commerce_line_item['currency_code'] = $commerce_line_item['commerce_total_currency_code'];
        unset($commerce_line_item['commerce_total_currency_code']);
        $commerce_line_item['data'] = unserialize($commerce_line_item['commerce_total_data']);
        unset($commerce_line_item['commerce_total_data']);
        // Add the percentage to the discount line item data component.
        if ($percentage) {
          foreach ($commerce_line_item['data']['components'] as $index => $component) {
            $component_name = explode('|', $component['name']);
            $discount_name = array_slice($component_name, -1);
            if (reset($discount_name) === $data['discount_name']) {
              $commerce_line_item['data']['components'][$index]['percentage'] = $percentage;
            }
          }
        }
        $discounts[] = $commerce_line_item;
      }
      $row->setSourceProperty('discount_commerce_total', $discounts);
    }

    $row->setSourceProperty('data', unserialize($row->getSourceProperty('data')));
    $row->setSourceProperty('title', $row->getSourceProperty('line_item_label'));

    // Get the product title from the commerce_product table.
    if ($row->getSourceProperty('type') === 'product') {
      $label = $row->getSourceProperty('line_item_label');
      $query = $this->select('commerce_product', 'cp')
        ->fields('cp', ['title'])
        ->condition('cp.sku', $label);
      $title = $query->execute()->fetchCol();
      $row->setSourceProperty('title', reset($title));
    }

    // Get Field API field values.
    $line_item_id = $row->getSourceProperty('line_item_id');
    $revision_id = $row->getSourceProperty('revision_id');
    foreach (array_keys($this->getFields('commerce_line_item', $row->getSourceProperty('type'))) as $field) {
      $row->setSourceProperty($field, $this->getFieldValues('commerce_line_item', $field, $line_item_id, $revision_id));
    }

    // Include the number of currency fraction digits in all prices.
    $currencyRepository = new CurrencyRepository();
    $prices = ['commerce_unit_price', 'commerce_total'];
    foreach ($prices as $price) {
      $value = $row->getSourceProperty($price);
      if ($value) {
        $currency_code = $value[0]['currency_code'];
        $value[0]['fraction_digits'] = $currencyRepository->get($currency_code)
          ->getFractionDigits();
        $row->setSourceProperty($price, $value);
      }
    }
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValues($entity_type, $field, $entity_id, $revision_id = NULL, $language = NULL) {
    $values = parent::getFieldValues($entity_type, $field, $entity_id, $revision_id, $language);
    // Unserialize any data blob in these fields.
    foreach ($values as $key => &$value) {
      if (isset($value['data'])) {
        $values[$key]['data'] = unserialize($value['data']);
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['line_item_id']['type'] = 'integer';
    return $ids;
  }

}
