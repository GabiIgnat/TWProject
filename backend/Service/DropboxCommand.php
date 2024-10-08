<?php

class DropboxCommand
{

    public function __construct()
    {
    }

    /**
     * @param $fileTempPath
     * @return ?string - the path of the file in dropbox, or null if the upload was unsuccessful
     */
    public function uploadFile($fileTempPath) : ?string {

        //if the access token is invalid, we generate a new one
        if(!TokenManager::IsValid()){
            TokenManager::generateNewToken();
        }

        if(!file_exists($fileTempPath)){
            return null;
        }

        $fp = fopen($fileTempPath, 'rb');
        if(!$fp) {//file doesn't exist or couldn't be opened
            return null;
        }
        $size = filesize($fileTempPath);
        $questionDAO = new PictureDAO();
        $fileNameInDropbox = $questionDAO->getMaxIdPicture() . '.jpg';

        $headers = array('Authorization: Bearer ' . TokenManager::$tokenAccessDROPBOX,
            'Content-Type: application/octet-stream',
            'Dropbox-API-Arg: {"path":"/pictures/'. $fileNameInDropbox . '", "mode":"add"}');

        $ch = curl_init('https://content.dropboxapi.com/2/files/upload');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, $size);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        $httpResponse = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        fclose($fp);

        if($httpResponse != 200){
            return null;
        }

        $response = json_decode($response, true);
        return $response['path_display'];
    }


    /**
     * @param $pathFileInDropbox
     * @return string - the direct download link of the file
     */
    public function getDownloadLink ($pathFileInDropbox) : string{

        $parameters = array('path' => $pathFileInDropbox);

        $headers = array('Authorization: Bearer ' . TokenManager::$tokenAccessDROPBOX,
            'Content-Type: application/json');

        $curlOptions = array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($parameters),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => true
        );

        $ch = curl_init('https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings');

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);
        return $this->getDirectDownloadLink($response['url']);

    }

    public function deleteFile($pathFileInDropbox) : bool {

        if(!TokenManager::isValid()){
            TokenManager::generateNewToken();
        }

        $parameters = array('path' => $pathFileInDropbox);

        $headers = array('Authorization: Bearer ' . TokenManager::$tokenAccessDROPBOX,
            'Content-Type: application/json');

        $curlOptions = array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($parameters),
            CURLOPT_RETURNTRANSFER => true
        );

        $ch = curl_init('https://api.dropboxapi.com/2/files/delete_v2');

        curl_setopt_array($ch, $curlOptions);

        curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status == 200;
    }

    private function getDirectDownloadLink($dropboxLink) : string {
        // Replace "www.dropbox.com" with "dl.dropboxusercontent.com"
        $modifiedLink = str_replace("www.dropbox.com", "dl.dropboxusercontent.com", $dropboxLink);

        // Append "?dl=1" to indicate direct download
        $directDownloadLink = substr_replace($modifiedLink , '1', -1, 1);

        return $directDownloadLink;
    }
}