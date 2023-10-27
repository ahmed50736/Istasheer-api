<?php

return [
    'email' => 'email',
    'phone' => 'phone',
    '404' => 'Requested url not found',
    '405' => 'Requested method not allowed',
    'login_failed' => 'Login failed',
    'file_upload' => 'File Uploaded Successfully',
    'data_not_found' => 'Requested data not found.',
    'update_message' => 'Updated successfully',
    'upload_message' => 'Uploaded successfully',
    'failed' => 'Failed',
    'social_password_change' => 'Unable to change password, beacuse your account registered with social account',
    'login_message' => 'Successfully logged in',
    'logout_message' => 'Successfully logged Out',
    'create_message' => 'Created Successfully',
    'delete_message' => 'Successfully deleted',
    'assign_message' => 'Successfully Assigned',
    'signed_up_failed' => 'Sign ',
    'account_verified' => 'Please verified your account first',
    'invalid_credentials' => 'wrong Email or Password',
    'server_error' => 'Server Error',
    'flag_message' => 'Your account has been flaged from :from to :to by admin. please contact support for more information',
    'duplicate_email' => 'already have account with this email',
    'account_delete' => 'Account Deleted Successfully',
    'try_again' => 'Something went wrong. Please try again later',
    'already_deleted' => 'File already deleted',

    'search_date' => 'Please Select a Date',
    //credentails send part
    'credentails_sending_message' => 'Hi, :username,\n You have been registered as :usertype in istesheer.\n Here is your credentails,\n Username-> :username \n Password:  :password \n use this credentails to login to your account & dont share your credentails to any one',
    
    /////otp part
    'otp_expired' => 'Given otp expired new otp send',
    'otp_send' => 'Otp send to your :attribute Successfully',
    'wrong_otp' => 'Wrong otp',
    'otp_confirmed' => 'Otp confimed',
    'otp_account_send' => 'Otp send to your email,please verify your account first',
    
    //users
    'user_not_found' => 'User not exist',
    'password_used' => 'Password already used',
    'login_failed' => 'email or password incorrect',
    'time_expired' => 'Time exipred',
    'unauthorized_user' => 'Unauthorized user',
    'unauthorized_acess' => 'Unauthorized access',
    'resend_otp' => 'Otp expired,new Otp send to your :attribute',
    'recongnized_message' => 'The :attribute dosent exist',
    'password_changed' => 'Password changed successfully',
    'invalid_token' => 'Your token is invalid',
    'token_expired' => 'Your token is expired',
    'wrong_current_password' => 'Given password doesnt matched to your current password',
    
    //voucher part
    'voucher_create' => 'Voucher created successfully',
    'voucher_delete' => 'Voucher deleted successfully',
    'voucher_update' => 'Voucher updated successfully',
    'voucher_not_found' => 'Voucher not found.',
    
    //case part
    'case_create' => 'case created successfully',
    'case_admin_message' => 'Dear Admin, you need to first remove attorney before you want to delete a case.',
    'cant_delete_case' => 'Attorney Assigned to Your case cant delete the case until remove attorney or complete the service',
    'case_not_exist' => 'Requested case not exist',
    'case_asigne_attorney' => 'Case asigne to this attorney successfully',
    'case_asigned_attorney' => 'Case already asigned to this attorney',
    'case_delete' => 'Successfully deleted all case information',
    'other_service_notification_title' => 'Other Service Created',
    'other_service_notification' => 'other sevice requested by user, please set a price for that service',
    'case_status_already_close' => 'Already case is :status',
    'case_status_close' => 'Successfully :status the case',
    
    //file part 
    'file_accept' => 'File accepted successfully',
    'file_reject' => 'File rejected Successfully',
    'file_delete' => 'File delted successfully',
    'file_not_found' => 'Requested file not found',
    'file_already_accepted' => "Response file already accepted",
    'file_already_rejected' => "Response file already rejected",

    //reminder part
    'reminder_create' => ' Reminder created successfully',
    'reminder_delete' => 'Reminder Deleted Successfully',
    'reminder_not_found' => 'Reminder Dosent exist',
    'reminder_update' => 'Reminder Updated Successfully',

    //case response
    'response_not_found' => 'Requested case response not found',

    //attorney part
    'remove_attorney' => 'Attorney removed from case successfully',
    'delete_Attorney' => 'Attorney deleted successfully',
    'create_Attorney' => 'Attorney created successfully',
    'attorney_not_found' => 'Attorney not found.',
    'attorney_not_found_case' => 'This attorney not asigned to this case',
    'attorney_percentage_assign' => 'Successfully asigned attorney percentage',
    'attorney_percentage_assign_update' => 'Successfully Updated attorney percentage',

    //flag part
    'flag_not_found' => 'User not found on flag list',
    'unfalg' => 'User unflaged successfully',
    'flaged' => 'User flaged successfully',
    'admin_create' => 'Admin created successfully',

    //terms part
    'terms_update' => 'Terms updated successfully',
    'terms_delete' => 'Terms deleted successfully',
    'terms_create' => 'Terms created successfully',
    'terms_not_found' => 'Terms not found',
    
    ///about part
    'about_update' => 'about updated successfully',
    'about_delete' => 'about deleted successfully',
    'about_create' => 'about created successfully',
    'about_not_found' => 'about not found',

    //price part 
    'price_update' => 'price updated Successfully',
    'price_not_found' => 'Requested data not found',
    'percentage_exist' => 'Already price asigned to this sub category, please try to update it',

    //case action
    'action_create' => 'case action created successfully',
    'action_delete' => 'case action deleted successfully',
    'action_update' => 'case action updated successfully ',
    'action_not_found' => 'Requested data not found',

    //case hearing
    'hearing_create' => 'hearing created successfully',
    'hearing_update' => 'hearing updated successfully',
    'hearing_delete' => 'hearing deleted successfully',

    //category-part
    'create_sub_category' => 'Sub-category created successfully',
    'delete_sub_category' => 'Sub-category deleted successfully',
    'not_found_sub_category' => 'Sub-category not exists',
    'update_sub_category' => 'Sub-category updated successfully',
    'password_reset_otp_phone' => 'Here is your one time otp :otp for reset password, which will be valid for only 5 minutes',
    'account_verification_otp_phone' => 'Here is your one time otp :otp for account verification, which will be valid for only 5 minutes',
    'otp_error' => 'problem with otp sending, please try again later',

    //account messages
    'admin_delete_account' => 'This account can be only deleted by Super Admin',
    'delete_account' => 'Account Deleted Successfully',

    //notification
    'reminder' => 'Reminder has been sent. Thank you',
    'notified' => 'Notified Successfully',
    'devices_not_found' => 'No device found',
    'image_not_found' => 'Image Not Found',

    //payment messages
    'invoice_create' => 'Successfully invoice created',
    'payment_confirmation' => 'Payment successfully completed',
    'invoice_expired' => 'Invoice Expired',
    'payment_failed' => 'payment failed, please try again',
    'other_case_price_not_set' => 'Price not asigned by admin yet',
    'price_not_matched' => 'Price not matched',
    'already_paid' => 'You have already paid the amount for the service',

    //case extra service
    'extra_service_create' => 'Successfully created extra service',

    //notification part
    'hearing_notification_title' => 'You have a new hearing',
    'hearing_notification_message' => 'Hello, You have a hearing to attend at :court on :date :time. chamber-no :chamber, room-number :room ,session-type- :session',

    //advance search
    'advance_search_error' => 'You need to pass al least one perameter to complete advance search',

    'faq' => [
        'create' => 'successfully created faq data',
        'update' => 'successfully updated faq data',
        'delete' => 'successfully deleted faq data'
    ],

    'attorney_credentilas_change' => [
        'reset_password' => 'password reseted successfully',
        'credentails_sending_message' => 'Hi, :username,\n Your Credentails has been reset.\n Here is your new credentails,\n Username-> :username \n Password:  :password \n use this credentails to login to your account & dont share your credentails with anyone',
        'success_mailer' => 'Credential sent to the attorney'
    ],

    'custom_validation' => [
        'wrong_user_type' => 'Invalid user to complete this action',
        'user_not_found' => 'User not found',
        'username_taken' => 'User name already taken',

    ],

    'guideline' => [
        'create' => 'Guideline created successfully',
        'update' => 'guideline updated successfully',
        'delete' => 'Guideline Deleted Successfully'
    ]


];
