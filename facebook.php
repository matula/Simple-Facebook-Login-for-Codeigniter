<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Name:  Simple Facebook Codeigniter Login
 * Author: Terry Matula
 *          terrymatula@gmail.com
 * @terrymatula
 * Created:  03.31.2011
 * Updated:  10.17.2014
 * Description:  An easy way to use Facebook to login
 *
 * Requirements: PHP5 or above
 *
 */
class Facebook extends CI_Controller
{

    public $appid;
    public $apisecret;
    public $scope = array();

    public function __construct()
    {
        parent::__construct();

        // Replace these with Application ID and Application Secret.
        $this->appid     = '12345';
        $this->apisecret = '123abc123';

        // Set your scopes
        $this->scope = array('email', 'publish_stream');
    }

    /**
     * If you have a Facebook login button on your site, link it here
     */
    public function index()
    {
        // Set the parameters for the Facebook url
        $query_params = array(
            'client_id'    => $this->appid,
            'redirect_uri' => site_url('facebook/confirm'),
            'scope'        => implode(',', $this->scope)
        );

        // Create the FB auth url to redirect the user to.
        $url = 'https://graph.facebook.com/oauth/authorize?' . http_build_query($query_params);
        redirect($url);
    }

    /**
     * Get tokens from FB then exchanges them for the User login tokens
     */
    public function confirm()
    {
        // get the code from the querystring
        $code = $this->input->get('code');
        if (!$code) {
            die('No code from Facebook');
        }

        // Set the parameters for getting the token
        $token_params = array(
            'client_id'     => $this->appid,
            'redirect_uri'  => site_url('facebook/confirm'),
            'client_secret' => $this->apisecret,
            'code'          => $code
        );

        // Get the auth token. '__getpage' is just a CURL method
        $gettoken = 'https://graph.facebook.com/oauth/access_token?' . http_build_query($token_params);
        $return   = $this->__getpage($gettoken);

        // If CURL didn't return a valid 200 http code, die
        if (!$return) {
            die('Error getting token');
        }

        // Put the token into the $access_token variable
        parse_str($return);

        // Now you can save the token to a database, and use it to access the user's graph
        // For example, this will return all their basic info.  check the FB Dev docs for more.
        $infourl = 'https://graph.facebook.com/me?access_token=' . $access_token;
        $return  = $this->__getpage($infourl);
        if (!$return) {
            die('Error getting info');
        }
        $info = json_decode($return);
        print_r($info);
    }

    /**
     * CURL method to interface with FB API
     * @param string $url
     * @return json
     */
    private function __getpage($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);

        // Check if it returns 200
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
            // You could also return the actual error, for logging or anything else
            $error = curl_error($ch);
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        return $return;
    }
}