<?php

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;

class SpotifyController extends Controller
{
    public function indexAction()
    {
    }

    public function homeAction()
    {
        $code = $_GET['code'];
        $client_id = "a299e9ca8f384fb4a7721ac5dc9a31ec";
        $client_secret = "cec6ce2e8ed649f7a38aa3ba2361663a";
        $url = "https://accounts.spotify.com";
        $headers = [
            'Content-type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($client_id . ":" . $client_secret)
        ];
        $client = new Client(
            [
                'base_uri' => $url,
                'headers' => $headers
            ]
        );
        $query = ["grant_type" => 'authorization_code', 'code' => $code, 'redirect_uri' => 'http://localhost:8080/spotify/home'];
        $response = $client->request('POST', '/api/token', ['form_params' => $query]);
        $response = $response->getBody();
        $response = json_decode($response, true);
        $access = $response['access_token'];

        $this->session->access = $access;

        $this->response->redirect('spotify/search');
    }

    public function searchAction()
    {
        $access = $this->session->get('access');
        $q = $this->request->getPost('name');
        $q = str_replace(' ', '-', $q);


        if ($this->request->getPost('search')) {
            $type = $this->request->getPost('type');
            $count = count($type);
            if ($count == 0) {

                $type = 'track';
                $this->view->response = $this->response($access, $q, $type);
            } else {
                if (in_array('albums', $type)) {
                    $this->view->response_album = $this->response($access, $q, 'album');
                }
                if (in_array('artists', $type)) {
                    $this->view->response_artist = $this->response($access, $q, 'artist');
                }
                if (in_array('tracks', $type)) {
                    $this->view->response_track = $this->response($access, $q, 'track');
                }
                if (in_array('playlists', $type)) {
                    $this->view->response_playlist = $this->response($access, $q, 'playlist');
                }
                if (in_array('episodes', $type)) {
                    $this->view->response_episode = $this->response($access, $q, 'episode');
                }
            }
        }
    }
    public function response($access, $q, $type)
    {
        $url = "https://api.spotify.com/v1/search?access_token=$access&q=$q&type=$type";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $response = json_decode($result, true);
        return $response;
    }
}
