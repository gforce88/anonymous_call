<?php
define('INVITE_TYPE_INVITER_PAY', 1);
define('INVITE_TYPE_INVITEE_PAY', 2);


define('INVITE_RESULT_INIT', 0);
define('INVITE_RESULT_INVITE', 1);
define('INVITE_RESULT_DECLINE', 2);
define('INVITE_RESULT_ACCEPT', 3);
define('INVITE_RESULT_NOCHECKOUT', 4);
define('INVITE_RESULT_CHECKOUT', 5);
define('INVITE_RESULT_NOPAY', 6);
define('INVITE_RESULT_PAYED', 7);

define('CALL_RESULT_INIT', 0);
define('CALL_RESULT_1STLEG_NOANSWER', 1);
define('CALL_RESULT_1STLEG_ANSWERMACHINE', 2);
define('CALL_RESULT_1STLEG_ANSWERED', 3);
define('CALL_RESULT_2NDLEG_NOANSWER', 4);
define('CALL_RESULT_2NDLEG_ANSWERED', 5);
define('CALL_RESULT_COMPLETED', 6);
define('CALL_RESULT_ERROR', -1);
