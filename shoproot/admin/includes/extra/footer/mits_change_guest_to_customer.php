<?php
/**
 * --------------------------------------------------------------
 * File: mits_change_guest_to_customer.php
 * Date: 25.04.2022
 * Time: 08:10
 *
 * Author: Hetfield
 * Copyright: (c) 2022 - MerZ IT-SerVice
 * Web: https://www.merz-it-service.de
 * Contact: info@merz-it-service.de
 * --------------------------------------------------------------
 */

if (defined('FILENAME_CUSTOMERS') && basename($PHP_SELF) == FILENAME_CUSTOMERS) {
  if (is_object($cInfo) && isset($cInfo->customers_id) && !empty($cInfo->customers_id)) {
    $MITS_GuestToCustomer = '';
    $MITS_GuestToCustomerButton = ($is_changed == 1 && $cInfo->customers_id == $mits_change_to_customer_id) ? false : true;
    if ($cInfo->account_type == 1 && $MITS_GuestToCustomerButton !== false) {
      switch ($_SESSION['language_code']) {
        case 'de':
          $mits_change_guest_to_customer_buttontext = 'Gastkonto zu Kundenkonto umwandeln und Passwort zusenden';
          break;
        default:
          $mits_change_guest_to_customer_buttontext = 'Convert guest account to customer account and send password';
          break;
      }
      $MITS_GuestToCustomer = '<table class="contentTable"><tbody><tr class="infoBoxHeading"><td class="infoBoxHeading">
<div id="mits_change_guest_to_customer">
<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action', 'send_password_mail')) . 'cID=' . $cInfo->customers_id . '&action=mits_change_to_customer&send_password_mail=yes') . '">' . $mits_change_guest_to_customer_buttontext . '</a>
</div></td></tr></tbody></table>';
      $MITS_GuestToCustomer = preg_replace("%(\r\n)|(\r)%", "", $MITS_GuestToCustomer);
      ?>
      <script>
        $(document).ready(function () {
          $('.boxRight .contentTable:last').after('<?php echo $MITS_GuestToCustomer;?>');
        });
      </script>
      <style>
        #mits_change_guest_to_customer {
          text-align: center;
          padding: 6px;
          background: #ffe;
          box-shadow: 0 0 0.4em #6a9;
          -moz-box-shadow: 0 0 0.4em #6a9;
          -webkit-box-shadow: 0 0 0.4em #6a9;
          border: 0.125em solid #ffe;
        }
      </style>
      <?php
    }
  }
}