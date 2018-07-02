<?php

namespace Drupal\Tests\civicrim_entity\Kernel;

use Drupal\civicrm_entity\CiviCrmApi;
use Drupal\civicrm_entity\Entity\CivicrmEntity;
use Drupal\civicrm_entity\Entity\Events;
use Drupal\civicrm_entity\SupportedEntities;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Unicode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the storage.
 *
 * @group civicrim_entity
 */
class CivicrmFieldConfigTest extends KernelTestBase {

  protected static $modules = [
    'civicrm',
    'civicrm_entity',
    'field',
    'text',
    'options',
    'link',
    'datetime',
  ];

  protected function setUp() {
    parent::setUp();

    require __DIR__ . '/../Type.php';

    $civicrm_api_mock = $this->prophesize(CiviCrmApi::class);
    $civicrm_api_mock->get('event', ['id' => 1])->willReturn($this->sampleGetEvents());
    $civicrm_api_mock->getFields("event")->willReturn($this->sampleGetFields());

    $supported_entities = SupportedEntities::getInfo();
    foreach ($supported_entities as $entity_type_id => $civicrm_entity_info) {
      $civicrm_entity_name = $civicrm_entity_info['civicrm entity name'];
      if ($civicrm_entity_name == 'event') {
        continue;
      }
      $civicrm_api_mock->getFields($civicrm_entity_name)->willReturn($this->minimalSampleFields());
    }
    $this->container->set('civicrm_entity.api', $civicrm_api_mock->reveal());

    $this->config('civicrm_entity.settings')
      ->set('enabled_entity_types', [
        'civicrm_event',
      ])->save();
  }

  /**
   * Make sure that creating a field does not explode the entity storage.
   */
  public function testCreateField() {
    // Create a field.
    $field_name = Unicode::strtolower($this->randomMachineName());
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'civicrm_event',
      'type' => 'string'
    ]);
    $field_storage->save();
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'civicrm_event',
      'label' => $this->randomMachineName() . '_label',
    ])->save();
  }

  public function testGet() {
    $result = $this->container->get('civicrm_entity.api')
      ->get('event', ['id' => 1]);
    $this->assertEquals('Fall Fundraiser Dinner', $result[0]['title']);
  }

  public function testSaveAndLoadFieldConfig() {
    $storage = $this->container->get('entity_type.manager')
      ->getStorage('civicrm_event');
    $entity = $storage->load(1);
    $this->assertInstanceOf(CivicrmEntity::class, $entity);
    $this->assertEquals($entity->id(), 1);
  }

  protected function minimalSampleFields() {
    return [
      'id' => [
        'name' => 'id',
        'type' => 1,
        'title' => 'Event ID',
        'description' => 'Event',
        'required' => TRUE,
        'table_name' => 'civicrm_event',
        'entity' => 'Event',
        'bao' => 'CRM_Event_BAO_Event',
        'localizable' => 0,
        'is_core_field' => TRUE,
        'api.aliases' => [
          0 => 'event_id',
        ],
      ],
    ];
  }

  /**
   * Json returned from sample Event getfields
   *
   * Gathered from http://dmaster.demo.civicrm.org/civicrm/api#explorer
   */
  protected function sampleGetFields() {
    return [
      'id' => [
        'name' => 'id',
        'type' => 1,
        'title' => 'Event ID',
        'description' => 'Event',
        'required' => TRUE,
        'table_name' => 'civicrm_event',
        'entity' => 'Event',
        'bao' => 'CRM_Event_BAO_Event',
        'localizable' => 0,
        'is_core_field' => TRUE,
        'api.aliases' => [
          0 => 'event_id',
        ],
      ],
      'summary' =>
        [
          'name' => 'summary',
          'type' => 32,
          'title' => 'Event Summary',
          'description' => 'Brief summary of event. Text and html allowed. Displayed on Event Registration form and can be used on other CMS pages which need an event summary.',
          'rows' => 4,
          'cols' => 60,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 4,
              'cols' => 60,
            ],
          'is_core_field' => TRUE,
        ],
      'event_type_id' =>
        [
          'name' => 'event_type_id',
          'type' => 1,
          'title' => 'Event Type',
          'description' => 'Event Type ID.Implicit FK to civicrm_option_value where option_group = event_type.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select',
              'size' => 6,
              'maxlength' => 14,
            ],
          'pseudoconstant' =>
            [
              'optionGroupName' => 'event_type',
              'optionEditPath' => 'civicrm/admin/options/event_type',
            ],
          'is_core_field' => TRUE,
        ],
      'participant_listing_id' =>
        [
          'name' => 'participant_listing_id',
          'type' => 1,
          'title' => 'Participant Listing',
          'description' => 'Should we expose the participant list? Implicit FK to civicrm_option_value where option_group = participant_listing.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select',
              'size' => 6,
              'maxlength' => 14,
            ],
          'pseudoconstant' =>
            [
              'optionGroupName' => 'participant_listing',
              'optionEditPath' => 'civicrm/admin/options/participant_listing',
            ],
          'is_core_field' => TRUE,
        ],
      'is_public' =>
        [
          'name' => 'is_public',
          'type' => 16,
          'title' => 'Is Event Public',
          'description' => 'Public events will be included in the iCal feeds. Access to private event information may be limited using ACLs.',
          'default' => '1',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'is_online_registration' =>
        [
          'name' => 'is_online_registration',
          'type' => 16,
          'title' => 'Is Online Registration',
          'description' => 'If true, include registration link on Event Info page.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'registration_link_text' =>
        [
          'name' => 'registration_link_text',
          'type' => 2,
          'title' => 'Event Registration Link Text',
          'description' => 'Text for link to Event Registration form which is displayed on Event Information screen when is_online_registration is true.',
          'maxlength' => 255,
          'size' => 45,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
        ],
      'registration_start_date' =>
        [
          'name' => 'registration_start_date',
          'type' => 12,
          'title' => 'Registration Start Date',
          'description' => 'Date and time that online registration starts.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select Date',
            ],
          'is_core_field' => TRUE,
        ],
      'registration_end_date' =>
        [
          'name' => 'registration_end_date',
          'type' => 12,
          'title' => 'Registration End Date',
          'description' => 'Date and time that online registration ends.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select Date',
            ],
          'is_core_field' => TRUE,
        ],
      'max_participants' =>
        [
          'name' => 'max_participants',
          'type' => 1,
          'title' => 'Max Participants',
          'description' => 'Maximum number of registered participants to allow. After max is reached, a custom Event Full message is displayed. If NULL, allow unlimited number of participants.',
          'default' => 'NULL',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Text',
              'size' => 6,
              'maxlength' => 14,
            ],
          'is_core_field' => TRUE,
        ],
      'event_full_text' =>
        [
          'name' => 'event_full_text',
          'type' => 32,
          'title' => 'Event Information',
          'description' => 'Message to display on Event Information page and INSTEAD OF Event Registration form if maximum participants are signed up. Can include email address/info about getting on a waiting list, etc. Text and html allowed.',
          'rows' => 4,
          'cols' => 60,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 4,
              'cols' => 60,
            ],
          'is_core_field' => TRUE,
        ],
      'is_monetary' =>
        [
          'name' => 'is_monetary',
          'type' => 16,
          'title' => 'Is this a PAID event?',
          'description' => 'If true, one or more fee amounts must be set and a Payment Processor must be configured for Online Event Registration.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'financial_type_id' =>
        [
          'name' => 'financial_type_id',
          'type' => 1,
          'title' => 'Financial Type',
          'description' => 'Financial type assigned to paid event registrations for this event. Required if is_monetary is true.',
          'default' => 'NULL',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select',
              'size' => 6,
              'maxlength' => 14,
            ],
          'pseudoconstant' =>
            [
              'table' => 'civicrm_financial_type',
              'keyColumn' => 'id',
              'labelColumn' => 'name',
            ],
          'is_core_field' => TRUE,
          'api.aliases' =>
            [
              0 => 'contribution_type_id',
            ],
        ],
      'payment_processor' =>
        [
          'name' => 'payment_processor',
          'type' => 2,
          'title' => 'Payment Processor',
          'description' => 'Payment Processors configured for this Event (if is_monetary is true)',
          'maxlength' => 128,
          'size' => 45,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select',
              'maxlength' => 128,
              'size' => 45,
            ],
          'pseudoconstant' =>
            [
              'table' => 'civicrm_payment_processor',
              'keyColumn' => 'id',
              'labelColumn' => 'name',
            ],
          'is_core_field' => TRUE,
        ],
      'is_map' =>
        [
          'name' => 'is_map',
          'type' => 16,
          'title' => 'Map Enabled',
          'description' => 'Include a map block on the Event Information page when geocode info is available and a mapping provider has been specified?',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'is_active' =>
        [
          'name' => 'is_active',
          'type' => 16,
          'title' => 'Is Active',
          'description' => 'Is this Event enabled or disabled/cancelled?',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
          'api.default' => 1,
        ],
      'fee_label' =>
        [
          'name' => 'fee_label',
          'type' => 2,
          'title' => 'Fee Label',
          'maxlength' => 255,
          'size' => 45,
          'import' => TRUE,
          'where' => 'civicrm_event.fee_label',
          'headerPattern' => '/^fee|(f(ee\s)?label)$/i',
          'export' => TRUE,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
        ],
      'is_show_location' =>
        [
          'name' => 'is_show_location',
          'type' => 16,
          'title' => 'show location',
          'description' => 'If true, show event location.',
          'default' => '1',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'loc_block_id' =>
        [
          'name' => 'loc_block_id',
          'type' => 1,
          'title' => 'Location Block ID',
          'description' => 'FK to Location Block ID',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_LocBlock',
          'is_core_field' => TRUE,
          'FKApiName' => 'LocBlock',
        ],
      'default_role_id' =>
        [
          'name' => 'default_role_id',
          'type' => 1,
          'title' => 'Default Role',
          'description' => 'Participant role ID. Implicit FK to civicrm_option_value where option_group = participant_role.',
          'import' => TRUE,
          'where' => 'civicrm_event.default_role_id',
          'export' => TRUE,
          'default' => '1',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select',
              'size' => 6,
              'maxlength' => 14,
            ],
          'pseudoconstant' =>
            [
              'optionGroupName' => 'participant_role',
              'optionEditPath' => 'civicrm/admin/options/participant_role',
            ],
          'is_core_field' => TRUE,
        ],
      'intro_text' =>
        [
          'name' => 'intro_text',
          'type' => 32,
          'title' => 'Introductory Message',
          'description' => 'Introductory message for Event Registration page. Text and html allowed. Displayed at the top of Event Registration form.',
          'rows' => 6,
          'cols' => 50,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 6,
              'cols' => 50,
            ],
          'is_core_field' => TRUE,
        ],
      'footer_text' =>
        [
          'name' => 'footer_text',
          'type' => 32,
          'title' => 'Footer Message',
          'description' => 'Footer message for Event Registration page. Text and html allowed. Displayed at the bottom of Event Registration form.',
          'rows' => 6,
          'cols' => 50,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 6,
              'cols' => 50,
            ],
          'is_core_field' => TRUE,
        ],
      'confirm_title' =>
        [
          'name' => 'confirm_title',
          'type' => 2,
          'title' => 'Confirmation Title',
          'description' => 'Title for Confirmation page.',
          'maxlength' => 255,
          'size' => 45,
          'default' => 'NULL',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
        ],
      'confirm_text' =>
        [
          'name' => 'confirm_text',
          'type' => 32,
          'title' => 'Confirm Text',
          'description' => 'Introductory message for Event Registration page. Text and html allowed. Displayed at the top of Event Registration form.',
          'rows' => 6,
          'cols' => 50,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 6,
              'cols' => 50,
            ],
          'is_core_field' => TRUE,
        ],
      'confirm_footer_text' =>
        [
          'name' => 'confirm_footer_text',
          'type' => 32,
          'title' => 'Footer Text',
          'description' => 'Footer message for Event Registration page. Text and html allowed. Displayed at the bottom of Event Registration form.',
          'rows' => 6,
          'cols' => 50,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 6,
              'cols' => 50,
            ],
          'is_core_field' => TRUE,
        ],
      'is_email_confirm' =>
        [
          'name' => 'is_email_confirm',
          'type' => 16,
          'title' => 'Is confirm email',
          'description' => 'If true, confirmation is automatically emailed to contact on successful registration.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'confirm_email_text' =>
        [
          'name' => 'confirm_email_text',
          'type' => 32,
          'title' => 'Confirmation Email Text',
          'description' => 'text to include above standard event info on confirmation email. emails are text-only, so do not allow html for now',
          'rows' => 4,
          'cols' => 50,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 4,
              'cols' => 50,
            ],
          'is_core_field' => TRUE,
        ],
      'confirm_from_name' =>
        [
          'name' => 'confirm_from_name',
          'type' => 2,
          'title' => 'Confirm From Name',
          'description' => 'FROM email name used for confirmation emails.',
          'maxlength' => 255,
          'size' => 45,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
        ],
      'confirm_from_email' =>
        [
          'name' => 'confirm_from_email',
          'type' => 2,
          'title' => 'Confirm From Email',
          'description' => 'FROM email address used for confirmation emails.',
          'maxlength' => 255,
          'size' => 45,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
        ],
      'cc_confirm' =>
        [
          'name' => 'cc_confirm',
          'type' => 2,
          'title' => 'Cc Confirm',
          'description' => 'comma-separated list of email addresses to cc each time a confirmation is sent',
          'maxlength' => 255,
          'size' => 45,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
        ],
      'bcc_confirm' =>
        [
          'name' => 'bcc_confirm',
          'type' => 2,
          'title' => 'Bcc Confirm',
          'description' => 'comma-separated list of email addresses to bcc each time a confirmation is sent',
          'maxlength' => 255,
          'size' => 45,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
        ],
      'default_fee_id' =>
        [
          'name' => 'default_fee_id',
          'type' => 1,
          'title' => 'Default Fee ID',
          'description' => 'FK to civicrm_option_value.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'is_core_field' => TRUE,
        ],
      'default_discount_fee_id' =>
        [
          'name' => 'default_discount_fee_id',
          'type' => 1,
          'title' => 'Default Discount Fee ID',
          'description' => 'FK to civicrm_option_value.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'is_core_field' => TRUE,
        ],
      'thankyou_title' =>
        [
          'name' => 'thankyou_title',
          'type' => 2,
          'title' => 'ThankYou Title',
          'description' => 'Title for ThankYou page.',
          'maxlength' => 255,
          'size' => 45,
          'default' => 'NULL',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
        ],
      'thankyou_text' =>
        [
          'name' => 'thankyou_text',
          'type' => 32,
          'title' => 'ThankYou Text',
          'description' => 'ThankYou Text.',
          'rows' => 6,
          'cols' => 50,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 6,
              'cols' => 50,
            ],
          'is_core_field' => TRUE,
        ],
      'thankyou_footer_text' =>
        [
          'name' => 'thankyou_footer_text',
          'type' => 32,
          'title' => 'Footer Text',
          'description' => 'Footer message.',
          'rows' => 6,
          'cols' => 50,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 6,
              'cols' => 50,
            ],
          'is_core_field' => TRUE,
        ],
      'is_pay_later' =>
        [
          'name' => 'is_pay_later',
          'type' => 16,
          'title' => 'Pay Later Allowed',
          'description' => 'if true - allows the user to send payment directly to the org later',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'pay_later_text' =>
        [
          'name' => 'pay_later_text',
          'type' => 32,
          'title' => 'Pay Later Text',
          'description' => 'The text displayed to the user in the main form',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'rows' => 2,
              'cols' => 80,
            ],
          'is_core_field' => TRUE,
        ],
      'pay_later_receipt' =>
        [
          'name' => 'pay_later_receipt',
          'type' => 32,
          'title' => 'Pay Later Receipt Text',
          'description' => 'The receipt sent to the user instead of the normal receipt text',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'rows' => 2,
              'cols' => 80,
            ],
          'is_core_field' => TRUE,
        ],
      'is_partial_payment' =>
        [
          'name' => 'is_partial_payment',
          'type' => 16,
          'title' => 'Partial Payments Enabled',
          'description' => 'is partial payment enabled for this event',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'initial_amount_label' =>
        [
          'name' => 'initial_amount_label',
          'type' => 2,
          'title' => 'Initial Amount Label',
          'description' => 'Initial amount label for partial payment',
          'maxlength' => 255,
          'size' => 45,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
        ],
      'initial_amount_help_text' =>
        [
          'name' => 'initial_amount_help_text',
          'type' => 32,
          'title' => 'Initial Amount Help Text',
          'description' => 'Initial amount help text for partial payment',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'rows' => 2,
              'cols' => 80,
            ],
          'is_core_field' => TRUE,
        ],
      'min_initial_amount' =>
        [
          'name' => 'min_initial_amount',
          'type' => 1024,
          'title' => 'Minimum Initial Amount',
          'description' => 'Minimum initial amount for partial payment',
          'precision' =>
            [
              0 => 20,
              1 => 2,
            ],
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Text',
              'size' => 6,
              'maxlength' => 14,
            ],
          'is_core_field' => TRUE,
        ],
      'is_multiple_registrations' =>
        [
          'name' => 'is_multiple_registrations',
          'type' => 16,
          'title' => 'Allow Multiple Registrations',
          'description' => 'if true - allows the user to register multiple participants for event',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'max_additional_participants' =>
        [
          'name' => 'max_additional_participants',
          'type' => 1,
          'title' => 'Maximum number of additional participants per registration',
          'description' => 'Maximum number of additional participants that can be registered on a single booking',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'is_core_field' => TRUE,
        ],
      'allow_same_participant_emails' =>
        [
          'name' => 'allow_same_participant_emails',
          'type' => 16,
          'title' => 'Does Event allow multiple registrations from same email address?',
          'description' => 'if true - allows the user to register multiple registrations from same email address.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'has_waitlist' =>
        [
          'name' => 'has_waitlist',
          'type' => 16,
          'title' => 'Waitlist Enabled',
          'description' => 'Whether the event has waitlist support.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'requires_approval' =>
        [
          'name' => 'requires_approval',
          'type' => 16,
          'title' => 'Requires Approval',
          'description' => 'Whether participants require approval before they can finish registering.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'expiration_time' =>
        [
          'name' => 'expiration_time',
          'type' => 1,
          'title' => 'Expiration Time',
          'description' => 'Expire pending but unconfirmed registrations after this many hours.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Text',
              'size' => 6,
              'maxlength' => 14,
            ],
          'is_core_field' => TRUE,
        ],
      'allow_selfcancelxfer' =>
        [
          'name' => 'allow_selfcancelxfer',
          'type' => 16,
          'title' => 'Allow Self-service Cancellation or Transfer',
          'description' => 'Allow self service cancellation or transfer for event?',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'selfcancelxfer_time' =>
        [
          'name' => 'selfcancelxfer_time',
          'type' => 1,
          'title' => 'Self-service Cancellation or Transfer Time',
          'description' => 'Number of hours prior to event start date to allow self-service cancellation or transfer.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Text',
              'size' => 6,
              'maxlength' => 14,
            ],
          'is_core_field' => TRUE,
        ],
      'waitlist_text' =>
        [
          'name' => 'waitlist_text',
          'type' => 32,
          'title' => 'Waitlist Text',
          'description' => 'Text to display when the event is full, but participants can signup for a waitlist.',
          'rows' => 4,
          'cols' => 60,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 4,
              'cols' => 60,
            ],
          'is_core_field' => TRUE,
        ],
      'approval_req_text' =>
        [
          'name' => 'approval_req_text',
          'type' => 32,
          'title' => 'Approval Req Text',
          'description' => 'Text to display when the approval is required to complete registration for an event.',
          'rows' => 4,
          'cols' => 60,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 4,
              'cols' => 60,
            ],
          'is_core_field' => TRUE,
        ],
      'is_template' =>
        [
          'name' => 'is_template',
          'type' => 16,
          'title' => 'Is an Event Template',
          'description' => 'whether the event has template',
          'required' => TRUE,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
          'api.default' => 0,
        ],
      'template_title' =>
        [
          'name' => 'template_title',
          'type' => 2,
          'title' => 'Event Template Title',
          'description' => 'Event Template Title',
          'maxlength' => 255,
          'size' => 45,
          'import' => TRUE,
          'where' => 'civicrm_event.template_title',
          'headerPattern' => '/(template.)?title$/i',
          'export' => TRUE,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
        ],
      'created_id' =>
        [
          'name' => 'created_id',
          'type' => 1,
          'title' => 'Created By Contact ID',
          'description' => 'FK to civicrm_contact, who created this event',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'is_core_field' => TRUE,
          'FKApiName' => 'Contact',
        ],
      'created_date' =>
        [
          'name' => 'created_date',
          'type' => 12,
          'title' => 'Event Created Date',
          'description' => 'Date and time that event was created.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'is_core_field' => TRUE,
        ],
      'currency' =>
        [
          'name' => 'currency',
          'type' => 2,
          'title' => 'Currency',
          'description' => '3 character string, value from config setting or input via user.',
          'maxlength' => 3,
          'size' => 4,
          'import' => TRUE,
          'where' => 'civicrm_event.currency',
          'headerPattern' => '/cur(rency)?/i',
          'dataPattern' => '/^[A-Z]{3}$/i',
          'export' => TRUE,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select',
              'maxlength' => 3,
              'size' => 4,
            ],
          'pseudoconstant' =>
            [
              'table' => 'civicrm_currency',
              'keyColumn' => 'name',
              'labelColumn' => 'full_name',
              'nameColumn' => 'name',
            ],
          'is_core_field' => TRUE,
        ],
      'campaign_id' =>
        [
          'name' => 'campaign_id',
          'type' => 1,
          'title' => 'Campaign',
          'description' => 'The campaign for which this event has been created.',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'FKClassName' => 'CRM_Campaign_DAO_Campaign',
          'html' =>
            [
              'type' => 'EntityRef',
              'size' => 6,
              'maxlength' => 14,
            ],
          'pseudoconstant' =>
            [
              'table' => 'civicrm_campaign',
              'keyColumn' => 'id',
              'labelColumn' => 'title',
            ],
          'is_core_field' => TRUE,
          'FKApiName' => 'Campaign',
        ],
      'is_share' =>
        [
          'name' => 'is_share',
          'type' => 16,
          'title' => 'Is shared through social media',
          'description' => 'Can people share the event through social media?',
          'default' => '1',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'is_confirm_enabled' =>
        [
          'name' => 'is_confirm_enabled',
          'type' => 16,
          'title' => 'Is the booking confirmation screen enabled?',
          'description' => 'If false, the event booking confirmation screen gets skipped',
          'default' => '1',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'parent_event_id' =>
        [
          'name' => 'parent_event_id',
          'type' => 1,
          'title' => 'Parent Event ID',
          'description' => 'Implicit FK to civicrm_event: parent event',
          'default' => 'NULL',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'EntityRef',
              'size' => 6,
              'maxlength' => 14,
            ],
          'is_core_field' => TRUE,
        ],
      'slot_label_id' =>
        [
          'name' => 'slot_label_id',
          'type' => 1,
          'title' => 'Subevent Slot Label ID',
          'description' => 'Subevent slot label. Implicit FK to civicrm_option_value where option_group = conference_slot.',
          'default' => 'NULL',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select',
              'size' => 6,
              'maxlength' => 14,
            ],
          'is_core_field' => TRUE,
        ],
      'dedupe_rule_group_id' =>
        [
          'name' => 'dedupe_rule_group_id',
          'type' => 1,
          'title' => 'Dedupe Rule',
          'description' => 'Rule to use when matching registrations for this event',
          'default' => 'NULL',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'FKClassName' => 'CRM_Dedupe_DAO_RuleGroup',
          'html' =>
            [
              'type' => 'Select',
              'size' => 6,
              'maxlength' => 14,
            ],
          'pseudoconstant' =>
            [
              'table' => 'civicrm_dedupe_rule_group',
              'keyColumn' => 'id',
              'labelColumn' => 'title',
              'nameColumn' => 'name',
            ],
          'is_core_field' => TRUE,
          'FKApiName' => 'RuleGroup',
        ],
      'is_billing_required' =>
        [
          'name' => 'is_billing_required',
          'type' => 16,
          'title' => 'Is billing block required',
          'description' => 'if true than billing block is required this event',
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'CheckBox',
            ],
          'is_core_field' => TRUE,
        ],
      'title' =>
        [
          'name' => 'title',
          'type' => 2,
          'title' => 'Event Title',
          'description' => 'Event Title (e.g. Fall Fundraiser Dinner)',
          'maxlength' => 255,
          'size' => 45,
          'import' => TRUE,
          'where' => 'civicrm_event.title',
          'headerPattern' => '/(event.)?title$/i',
          'export' => TRUE,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'Text',
              'maxlength' => 255,
              'size' => 45,
            ],
          'is_core_field' => TRUE,
          'uniqueName' => 'event_title',
        ],
      'description' =>
        [
          'name' => 'description',
          'type' => 32,
          'title' => 'Event Description',
          'description' => 'Full description of event. Text and html allowed. Displayed on built-in Event Information screens.',
          'rows' => 8,
          'cols' => 60,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 1,
          'html' =>
            [
              'type' => 'TextArea',
              'rows' => 8,
              'cols' => 60,
            ],
          'is_core_field' => TRUE,
          'uniqueName' => 'event_description',
        ],
      'start_date' =>
        [
          'name' => 'start_date',
          'type' => 12,
          'title' => 'Event Start Date',
          'description' => 'Date and time that event starts.',
          'import' => TRUE,
          'where' => 'civicrm_event.start_date',
          'headerPattern' => '/^start|(s(tart\s)?date)$/i',
          'export' => TRUE,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select Date',
            ],
          'is_core_field' => TRUE,
          'uniqueName' => 'event_start_date',
        ],
      'end_date' =>
        [
          'name' => 'end_date',
          'type' => 12,
          'title' => 'Event End Date',
          'description' => 'Date and time that event ends. May be NULL if no defined end date/time',
          'import' => TRUE,
          'where' => 'civicrm_event.end_date',
          'headerPattern' => '/^end|(e(nd\s)?date)$/i',
          'export' => TRUE,
          'table_name' => 'civicrm_event',
          'entity' => 'Event',
          'bao' => 'CRM_Event_BAO_Event',
          'localizable' => 0,
          'html' =>
            [
              'type' => 'Select Date',
            ],
          'is_core_field' => TRUE,
          'uniqueName' => 'event_end_date',
        ],
    ];
  }

  protected function sampleGetEvents() {
    return [
      0 => [
        'id' => '1',
        'title' => 'Fall Fundraiser Dinner',
        'event_title' => 'Fall Fundraiser Dinner',
        'summary' => 'Kick up your heels at our Fall Fundraiser Dinner/Dance at Glen Echo Park! Come by yourself or bring a partner, friend or the entire family!',
        'description' => 'This event benefits our teen programs. Admission includes a full 3 course meal and wine or soft drinks. Grab your dancing shoes, bring the kids and come join the party!',
        'event_description' => 'This event benefits our teen programs. Admission includes a full 3 course meal and wine or soft drinks. Grab your dancing shoes, bring the kids and come join the party!',
        'event_type_id' => '3',
        'participant_listing_id' => '1',
        'is_public' => '1',
        'start_date' => '2018-05-02 17:00:00',
        'event_start_date' => '2018-05-02 17:00:00',
        'end_date' => '2018-05-04 17:00:00',
        'event_end_date' => '2018-05-04 17:00:00',
        'is_online_registration' => '1',
        'registration_link_text' => 'Register Now',
        'max_participants' => '100',
        'event_full_text' => 'Sorry! The Fall Fundraiser Dinner is full. Please call Jane at 204 222-1000 ext 33 if you want to be added to the waiting list.',
        'is_monetary' => '1',
        'financial_type_id' => '4',
        'payment_processor' => '1',
        'is_map' => '1',
        'is_active' => '1',
        'fee_label' => 'Dinner Contribution',
        'is_show_location' => '1',
        'loc_block_id' => '1',
        'default_role_id' => '1',
        'intro_text' => 'Fill in the information below to join as at this wonderful dinner event.',
        'confirm_title' => 'Confirm Your Registration Information',
        'confirm_text' => 'Review the information below carefully.',
        'is_email_confirm' => '1',
        'confirm_email_text' => 'Contact the Development Department if you need to make any changes to your registration.',
        'confirm_from_name' => 'Fundraising Dept.',
        'confirm_from_email' => 'development@example.org',
        'thankyou_title' => 'Thanks for Registering!',
        'thankyou_text' => 'Thank you',
        'is_pay_later' => '1',
        'pay_later_text' => 'I will send payment by check',
        'pay_later_receipt' => 'Send a check payable to Our Organization within 3 business days to hold your reservation. Checks should be sent to: 100 Main St., Suite 3, San Francisco CA 94110',
        'is_partial_payment' => '0',
        'is_multiple_registrations' => '1',
        'max_additional_participants' => '0',
        'allow_same_participant_emails' => '0',
        'allow_selfcancelxfer' => '0',
        'selfcancelxfer_time' => '0',
        'is_template' => '0',
        'currency' => 'USD',
        'is_share' => '1',
        'is_confirm_enabled' => '1',
        'is_billing_required' => '0',
        'contribution_type_id' => '4',
      ],
    ];
  }
}
