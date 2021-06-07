<?php
/**
 * Created by PhpStorm.
 * User: monzer
 * Date: 6/6/21
 * Time: 2:23 PM
 */

namespace App\Http\Controllers;
use GuzzleHttp\Client;


class GalleryController extends Controller
{
    private $token;
    private $uri;
    private $flickrApiKey;



    public function __construct()
    {
        /** TODO: Put this settings in env file */
        $api_user = 'monzerhossain@yahoo.com';
        $api_password = 'tesr1234';
        $this->uri = 'http://gallery-admin:8000/api/';
        $this->token = $this->getToken($api_user, $api_password);
        $this->flickrApiKey = 'a5565b330800a1643fff2ad86c081579';
    }

    public function viewImages($category=null){
        $categories = $this->getCategories();
        if(!$category)
            $category = $categories[0]['name'];
        $imageData = $this->getFlickrImageUrls($category);
        $imageUrls = json_decode($this->parseDataIntoImageURL($imageData), true)['imageURL'];
        return view('images', compact('categories', 'imageUrls', 'category'));
    }

    private function getCategories(){
        $client = new Client(array('base_uri' => $this->uri));
        $headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];
        $response = $client->request('GET', 'categories', array(
            'headers' => $headers,
            ) );


        $result = json_decode($response->getBody()->getContents(), true);
        return $result;

    }

    private function  getFlickrImageUrls($searchString){
        //Flickr API url
        $URL = "https://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=".$this->flickrApiKey."&tags='" . $searchString ."'&format=rest&per_page=500";
        $client = new Client();
        $response = $client->get($URL);
        $result = $response->getBody()->getContents();

        return $result;
    }

    private function getToken($user, $password){

        $client = new Client(array('base_uri' => $this->uri));
        $response = $client->request('POST', 'login', array(
            'form_params' =>
                array(
                    'email' => $user,
                    'password' => $password
                )) );


        $result = json_decode($response->getBody()->getContents(), true);
        return $result['access_token'];
    }

    //This parses the XML response and extracts the image URL information
    private function parseDataIntoImageURL($data){
        $imageData=simplexml_load_string($data) or die("Error: Cannot create object");
        $imageURLs = "{ \"imageURL\" : [";
        $first = true;
        foreach($imageData->photos->photo as $photo){
            foreach($photo->attributes() as $key => $value){
                if( $key  == "farm" )
                    $farmId = $value;
                else if($key == "server")
                    $serverId = $value;
                else if($key == "id")
                    $id = $value;
                else if($key == "secret")
                    $secret = $value;
            }

            if($first){
                $imageURLs .= "\"https://farm" . $farmId . ".staticflickr.com/" . $serverId . "/" . $id . "_" . $secret . "\" ";
                $first = false;
            }
            else
                $imageURLs .= ", \"https://farm" . $farmId . ".staticflickr.com/" . $serverId . "/" . $id . "_" . $secret . "\" ";
        }
        $imageURLs .= "] }";
        return $imageURLs;

    }

}