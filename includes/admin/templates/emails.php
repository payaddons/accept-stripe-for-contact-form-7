<?php

use CF7PA_Pay_Addons\Email\Mailer;
use CF7PA_Pay_Addons\Shared\Security_Utils;
use CF7PA_Pay_Addons\Stripe\Stripe_Webhook_State;

Mailer::init_emails();
$emails = get_option('cf7pa_email_list');
$email_settings = Mailer::get_email_setting();
$webhook_no_event = Stripe_Webhook_State::is_no_event_received();
?>

<div id="emails" class="cf7pa-tab-panel p-8">
  <div class="flex justify-between items-center pb-5">
    <div>
      <h4>Email notifications</h4>
    </div>
    <label class="relative inline-flex items-center mr-5 cursor-pointer">
      <strong>Note: </strong>This feature requires a webhook URL to be configured. 
    </label>
  </div>
  <div class="py-4">
    <?php if($webhook_no_event) { ?>
    <div class="flex items-center p-2 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
      <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
      </svg>
      <span class="sr-only">Info</span>
      <div class="ml-3 text-sm font-medium">
        Ensure the webhook URL configure successfully.
      </div>
    </div>
    <?php } ?>

    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th scope="col">Email</th>
          <th scope="col">Content type</th>
          <th scope="col">Recipient(s)</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($emails as $key => $email) { ?>
          <tr data-id="<?php echo $email['id'] ?>">
            <td scope="row">
              <div class="flex items-center">
                <?php if ($email['enabled']) { ?>
                  <span class="cf7pa-pill cf7pa-pill-enable">enable</span>
                <?php } else { ?>
                  <span class="cf7pa-pill cf7pa-pill-disable">disabled</span>
                <?php } ?>
                <span class="name"><?php echo $email['name'] ?></span>
                <?php echo cf7pa_help_tip($email['description']) ?>
              </div>
            </td>
            <td scope="row"><?php echo $email['type'] ?></td>
            <td scope="row"><span class="recipients"><?php echo $email['recipients'] ?></span></td>
            <td scope="row"><a data-modal-target="edit-email-modal" data-title="<?php echo $email['name'] ?>" data-id="<?php echo $email['id'] ?>" class="btn-edit-email button muted-button" href="#">Manage</a></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>

    <div class="my-4 text-xl font-bold">Email sender options</div>
    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
      <div class="w-full">
        <label for="fromName" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">From Name <?php echo cf7pa_help_tip('How the sender name appears in outgoing emails.') ?></label>
        <input type="text" value="<?php echo $email_settings['fromName'] ?>" name="fromName" id="fromName" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
      </div>
      <div class="w-full">
        <label for="fromEmail" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">From Email <?php echo cf7pa_help_tip('How the sender email appears in outgoing emails.') ?></label>
        <input type="text" value="<?php echo $email_settings['fromEmail'] ?>" name="fromEmail" id="fromEmail" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      </div>
    </div>
  </div>
  <div class="flex justify-between items-center">
    <a class="cf7pa-button-link" href="https://cf7-docs.payaddons.com/basics/email-notification-pro" target="_blank" rel="external noreferrer noopener">How to customize email?</a>
    <button type="button" class="btn-save-email-setting cf7pa-button-primary">Save changes</button>
  </div>
  <div id="alert"></div>
</div>

<div id="edit-email-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative w-full max-w-2xl max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
      <!-- Modal header -->
      <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Modal</h3>
        <button id="edit-email-modal-close-btn" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-email-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <div class="p-6 space-y-6">
        <form action="#" id="cf7pa-email-form">
          <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
            <div class="sm:col-span-2">
              <span class="mr-3 text-sm font-medium text-gray-900 dark:text-gray-300">Enable/Disable</span>
              <label class="relative inline-flex items-center cursor-pointer">
                <input name="enabled" type="checkbox" value="" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
              </label>
            </div>
            <div class="sm:col-span-2">
              <label for="subject" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Subject</label>
              <input type="text" name="subject" id="subject" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
            </div>
            <div class="w-full">
              <label for="recipients" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Recipient(s)</label>
              <input type="text" name="recipients" id="recipients" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
            </div>
            <div class="w-full">
              <label for="headerText" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Header Text</label>
              <input type="text" name="headerText" id="headerText" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            </div>
            <div class="sm:col-span-2">
              <label for="htmlContent" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Template</label>
              <?php wp_editor('', 'htmlContent') ?>
            </div>
          </div>
          <div class="flex justify-end mt-2">
            <button name="btnSaveEmail" class="btn-save-email cf7pa-button-primary" type="submit">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-40 hidden">modal backdrop</div>