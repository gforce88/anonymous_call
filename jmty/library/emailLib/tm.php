<?php
require_once 'AppEmail.php';

$appEmails = new AppEmails ("smtp.gmail.com",465,"jmty-notifications@incognitosys.com","jjmmyy*913");

$appEmails->sendTherapistNotAvailEmail ('g_szeto@incognitosys.com');

$appEmails->sendThankYouEmail ('g_szeto@incognitosys.com',60,5000);

$appEmails->sendCardErrEmail ('g_szeto@incognitosys.com');

