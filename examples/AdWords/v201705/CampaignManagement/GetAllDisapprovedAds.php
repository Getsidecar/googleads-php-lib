<?php
/**
 * This example gets all disapproved ads in an ad group. To get ad groups, run
 * BasicOperation/GetAdGroups.php.
 *
 * Copyright 2016, Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package    GoogleApiAdsAdWords
 * @subpackage v201705
 * @category   WebServices
 * @copyright  2016, Google Inc. All Rights Reserved.
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License,
 *             Version 2.0
 */

// Include the initialization file
require_once dirname(dirname(__FILE__)) . '/init.php';

// Enter parameters required by the code example.
$adGroupId = 'INSERT_AD_GROUP_ID_HERE';

/**
 * Runs the example.
 * @param AdWordsUser $user the user to run the example with
 * @param string $adGroupId the parent ad group id of the ads to retrieve
 */
function GetAllDisapprovedAdsExample(AdWordsUser $user, $adGroupId) {
  // Get the service, which loads the required classes.
  $adGroupAdService = $user->GetService('AdGroupAdService', ADWORDS_VERSION);

  // Create selector.
  $selector = new Selector();
  $selector->fields = array('Id', 'PolicySummary');
  $selector->ordering = array(new OrderBy('Id', 'ASCENDING'));

  // Create predicates.
  $selector->predicates[] = new Predicate('AdGroupId', 'IN', array($adGroupId));

  // Create paging controls.
  $selector->paging = new Paging(0, AdWordsConstants::RECOMMENDED_PAGE_SIZE);

  $disapprovedAdsCount = 0;
  do {
    // Make the get request.
    $page = $adGroupAdService->get($selector);

    // Display results.
    if (isset($page->entries)) {
      foreach ($page->entries as $adGroupAd) {
        if ($adGroupAd->policySummary->combinedApprovalStatus
            !== 'DISAPPROVED') {
          // Skip ad group ads that are not disapproved.
          continue;
        }

        $disapprovedAdsCount++;
        printf(
            "Ad with ID %d, and type '%s' was disapproved with the "
                . "following policy topic entries:\n",
            $adGroupAd->ad->id,
            $adGroupAd->ad->AdType
        );
        foreach ($adGroupAd->policySummary->policyTopicEntries
            as $policyTopicEntry) {
          printf(
              "  topic id: %s, topic name: '%s'\n",
              $policyTopicEntry->policyTopicId,
              $policyTopicEntry->policyTopicName
          );
        }
      }
    }

    // Advance the paging index.
    $selector->paging->startIndex += AdWordsConstants::RECOMMENDED_PAGE_SIZE;
  } while ($page->totalNumEntries > $selector->paging->startIndex);
  printf("%d disapproved ads were found.\n", $disapprovedAdsCount);
}

// Don't run the example if the file is being included.
if (__FILE__ != realpath($_SERVER['PHP_SELF'])) {
  return;
}

try {
  // Get AdWordsUser from credentials in "../auth.ini"
  // relative to the AdWordsUser.php file's directory.
  $user = new AdWordsUser();

  // Log every SOAP XML request and response.
  $user->LogAll();

  // Run the example.
  GetAllDisapprovedAdsExample($user, $adGroupId);
} catch (Exception $e) {
  printf("An error has occurred: %s\n", $e->getMessage());
}
