<?php
/**
 * --------------------------------------------------------------
 * File: mits_change_guest_to_customer.php
 * Date: 25.04.2022
 * Time: 10:38
 *
 * Author: Hetfield
 * Copyright: (c) 2022 - MerZ IT-SerVice
 * Web: https://www.merz-it-service.de
 * Contact: info@merz-it-service.de
 * --------------------------------------------------------------
 */

if (defined('FILENAME_CUSTOMERS') && basename($PHP_SELF) == FILENAME_CUSTOMERS) {
  $is_changed = $mits_change_to_customer_id = 0;
  if (isset($_GET['action']) && $_GET['action'] == 'mits_change_to_customer' && isset($_GET['cID']) && $_GET['cID'] != '') {
    $mits_change_to_customer_id = (int)$_GET['cID'];
    $check_email_1 = xtc_db_query("SELECT customers_email_address FROM " . TABLE_CUSTOMERS . " WHERE customers_id = " . $mits_change_to_customer_id);
    if (xtc_db_num_rows($check_email_1)) {
      $check_email_1 = xtc_db_fetch_array($check_email_1);
      $check_email_2 = xtc_db_query("SELECT customers_email_address,
                                            customers_firstname,
                                            customers_lastname,
                                            customers_gender,
                                            customers_cid
                                       FROM " . TABLE_CUSTOMERS . "
                                      WHERE customers_email_address = '" . xtc_db_input($check_email_1['customers_email_address']) . "'
                                        AND account_type = 0
                                        AND customers_id != " . $mits_change_to_customer_id);
      if (xtc_db_num_rows($check_email_2)) {
        $messageStack->add_session(WARNING_CUSTOMER_ALREADY_EXISTS, 'warning');
      } else {
        require_once(DIR_FS_INC . 'xtc_encrypt_password.inc.php');
        require_once(DIR_FS_INC . 'xtc_create_password.inc.php');
        require_once(DIR_FS_INC . 'xtc_php_mail.inc.php');
        require_once(DIR_FS_INC . 'generate_customers_cid.inc.php');
        $customers_status = (defined('DEFAULT_CUSTOMERS_STATUS_ID') && DEFAULT_CUSTOMERS_STATUS_ID != 0) ? DEFAULT_CUSTOMERS_STATUS_ID : 2;
        $customers_password_encrypted = xtc_RandomString(8);
        $customers_password = xtc_encrypt_password($customers_password_encrypted);
        $sql_data_update_array = array(
          'customers_cid'           => ((defined('MODULE_CUSTOMERS_CID_STATUS') && MODULE_CUSTOMERS_CID_STATUS == 'true') ? generate_customers_cid(true) : $check_email_1['customers_cid']),
          'customers_password'      => $customers_password,
          'account_type'            => 0,
          'customers_status'        => $customers_status,
          'customers_last_modified' => 'now()'
        );
        xtc_db_perform(TABLE_CUSTOMERS, $sql_data_update_array, 'update', "customers_id = " . $mits_change_to_customer_id);
        $is_changed = 1;

        if (isset($_GET['send_password_mail']) && $_GET['send_password_mail'] == 'yes') {
          $smarty = new Smarty;
          $smarty->template_dir = DIR_FS_CATALOG . 'templates';
          $smarty->compile_dir = DIR_FS_CATALOG . 'templates_c';
          $smarty->config_dir = DIR_FS_CATALOG . 'lang';

          $smarty->assign('tpl_path', HTTP_SERVER . DIR_WS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/');
          $smarty->assign('logo_path', HTTP_SERVER . DIR_WS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/img/');
          $smarty->assign('GENDER', $customers_gender);
          $smarty->assign('FIRSTNAME', $check_email_1['customers_firstname']);
          $smarty->assign('LASTNAME', $check_email_1['customers_lastname']);
          $smarty->assign('NAME', $check_email_1['customers_firstname'] . ' ' . $check_email_1['customers_lastname']);
          $smarty->assign('EMAIL', $check_email_1['customers_email_address']);
          $smarty->assign('COMMENTS', '');
          $smarty->assign('PASSWORD', $customers_password_encrypted);

          $smarty->caching = 0;
          $smarty->assign('language', $_SESSION['language']);
          $html_mail = $smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/' . $_SESSION['language'] . '/create_account_mail.html');
          $txt_mail = $smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/' . $_SESSION['language'] . '/create_account_mail.txt');

          xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
            EMAIL_SUPPORT_NAME,
            $check_email_1['customers_email_address'],
            $check_email_1['customers_firstname'] . ' ' . $check_email_1['customers_lastname'],
            EMAIL_SUPPORT_FORWARDING_STRING,
            EMAIL_SUPPORT_REPLY_ADDRESS,
            EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
            '',
            '',
            EMAIL_SUPPORT_SUBJECT,
            $html_mail,
            $txt_mail);
        }
      }
    }
    xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action', 'mits_change_to_customer', 'send_password_mail')) . 'cID=' . $mits_change_to_customer_id));
  }
}