<?php
/**
 * Created by PhpStorm.
 * User: hakanbaysal
 * Date: 28.10.2018
 * Time: 02:06
 */

class Analytics
{
    private $DB;
    private $counterArr = array();

    public function __construct()
    {
        $this->DB = new DB();
        $analytics = $this->initializeAnalytics();
        $profile = $this->getFirstProfileId($analytics);
        $results = $this->getResults($analytics, $profile);
        $this->printResults($results);
    }

    public function initializeAnalytics()
    {
        // Creates and returns the Analytics Reporting service object.
        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        $KEY_FILE_LOCATION = __DIR__ . '/analytics-6980456c450e.json';

        // Create and configure a new client object.
        $client = new Google_Client();
        $client->setApplicationName("Hello Analytics Reporting");
        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $analytics = new Google_Service_Analytics($client);

        return $analytics;
    }

    public function getFirstProfileId($analytics)
    {
        // Get the user's first view (profile) ID.
        // Get the list of accounts for the authorized user.
        $accounts = $analytics->management_accounts->listManagementAccounts();

        if (count($accounts->getItems()) > 0) {
            $items = $accounts->getItems();
            $firstAccountId = $items[0]->getId();

            // Get the list of properties for the authorized user.
            $properties = $analytics->management_webproperties
                ->listManagementWebproperties($firstAccountId);

            if (count($properties->getItems()) > 0) {
                $items = $properties->getItems();
                $firstPropertyId = $items[0]->getId();

                // Get the list of views (profiles) for the authorized user.
                $profiles = $analytics->management_profiles
                    ->listManagementProfiles($firstAccountId, $firstPropertyId);

                if (count($profiles->getItems()) > 0) {
                    $items = $profiles->getItems();

                    // Return the first view (profile) ID.
                    return $items[0]->getId();

                } else {
                    throw new Exception('No views (profiles) found for this user.');
                }
            } else {
                throw new Exception('No properties found for this user.');
            }
        } else {
            throw new Exception('No accounts found for this user.');
        }
    }

    public function getResults($analytics, $profileId)
    {
        // Calls the Core Reporting API and queries for the number of sessions
        // for the last seven days.
        try {
            $params = $this->DB->fetchObj("SELECT id,`key`,value FROM params ORDER BY `key`", "Params");
        } catch (PDOException $e) {
            echo __LINE__ . $e->getMessage();
        }

        $dimensions = array();
        $segments = array();
        $metrics = array();

        foreach ($params as $param) {
            $id = $param->id;
            $key = $param->key;
            $value = $param->value;

            switch ($key) {
                case 'dimensions':
                    array_push($this->counterArr, $id);
                    array_push($dimensions, $value);
                    break;
                case 'segment':
                    array_push($segments, $value);
                    break;
                case 'metrics':
                    array_push($this->counterArr, $id);
                    array_push($metrics, $value);
                    break;
            }
        }

        $dimensions = implode(',', $dimensions);
        $segments = implode(';', $segments);
        $metrics = implode(',', $metrics);

        $optParams = array(
            'dimensions' => $dimensions,
            'segment' => $segments
        );

        return $analytics->data_ga->get(
            'ga:' . $profileId,
            '30daysAgo',
            'today',
            $metrics,
            $optParams
        );
    }

    public function printResults($results)
    {
        // Parses the response from the Core Reporting API and prints
        // the profile name and total sessions.
        if (count($results->getRows()) > 0) {
            // Get the profile name.
            $profileName = $results->getProfileInfo()->getProfileName();
            // Get the entry for the first entry in the first row.
            $rows = $results->getRows();
            //var_dump($this->counterArr);

            try {
                foreach ($rows as $row) {
                    foreach ($row as $key => $value) {
                        //echo $key.' | '.$this->counterArr[$key].' - '.$value.'<br />';
                        $Datas = new Datas();
                        $Datas->setParamId($this->counterArr[$key]);
                        $Datas->setResult($value);

                        $this->DB->insertViaObj($Datas);
                    }
                }
            } catch (PDOException $e) {
                echo __LINE__ . $e->getMessage();
            }

            echo '<pre>';
            print_r($rows);
            echo '</pre>';
        } else {
            print "No results found.\n";
        }
    }
}
