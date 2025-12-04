<?php

namespace Dcblogdev\Dropbox\Resources;

use Dcblogdev\Dropbox\Dropbox;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Exception;
use FileNotFoundException;

use function PHPUnit\Framework\throwException;
use function trigger_error;

class Files extends Dropbox
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listContents($path = '')
    {
        $pathRequest = $this->forceStartingSlash($path);

        return $this->post('files/list_folder', [
            'path' => $path == '' ? '' : $pathRequest
        ]);
    }

    public function listContentsContinue($cursor = '')
    {
        return $this->post('files/list_folder/continue', [
            'cursor' => $cursor
        ]);
    }

    public function move($fromPath, $toPath, $autoRename = false, $allowOwnershipTransfer = false)
    {
        $this->post('files/move_v2', [
            "from_path" => $fromPath,
            "to_path" => $toPath,
            "autorename" => $autoRename,
            "allow_ownership_transfer" => $allowOwnershipTransfer
        ]);
    }

    public function delete($path)
    {
        $path = $this->forceStartingSlash($path);

        return $this->post('files/delete_v2', [
            'path' => $path
        ]);
    }

    public function createFolder($path)
    {
        $path = $this->forceStartingSlash($path);

        return $this->post('files/create_folder', [
            'path' => $path
        ]);
    }

    public function search($query)
    {
        return $this->post('files/search', [
            'path' => '',
            'query' => $query,
            'start' => 0,
            'max_results' => 1000,
            'mode' => 'filename'
        ]);
    }

    public function uploadAs($path, $targetFileName, $sourceFilePath, $mode = 'add')
    {
        if ($sourceFilePath == '') {
            throw new Exception('File is required');
        } elseif (!file_exists($sourceFilePath)) {
            throw new FileNotFoundException('File does not exist: ' . $sourceFilePath);
        }

	    $path     = ($path !== '') ? $this->forceStartingSlash($path) : '';
	    $contents = $this->getContents($sourceFilePath);
        $filename = $targetFileName ?? $this->getFilenameFromPath($sourceFilePath);
        $path     = $path . DIRECTORY_SEPARATOR . $filename;

        try {

            $client = new Client;

            $response = $client->post('https://content.dropboxapi.com/2/files/upload', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/octet-stream',
                    'Dropbox-API-Arg' => json_encode([
                        "path" => $path,
                        "mode" => $mode,
                        "autorename" => true,
                        "mute" => false
                    ])
                ],
                'body' => $contents
            ]);

            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function upload($path, $uploadPath, $mode = 'add')
    {
        $this->uploadAs($path, null, $uploadPath, $mode);
    }

    public function download($path, $destFolder = '')
    {
        $path = $this->forceStartingSlash($path);

        try {
            $client = new Client;

            $response = $client->post("https://content.dropboxapi.com/2/files/download", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Dropbox-API-Arg' => json_encode([
                        'path' => $path
                    ])
                ]
            ]);

            $header = json_decode($response->getHeader('Dropbox-Api-Result')[0], true);
            $body = $response->getBody()->getContents();

            if (empty($destFolder)){
                $destFolder = 'dropbox-temp';

                if (! is_dir($destFolder)) {
                    mkdir($destFolder);
                }
            }

            file_put_contents($destFolder.$header['name'], $body);

            return response()->download($destFolder.$header['name'], $header['name'])->deleteFileAfterSend();

        } catch (ClientException $e) {
            throw new Exception($e->getResponse()->getBody()->getContents());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getContentsFile($path)
    {
        $path = $this->forceStartingSlash($path);

        try {
            $client = new Client;

            $response = $client->post("https://content.dropboxapi.com/2/files/download", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Dropbox-API-Arg' => json_encode([
                                                         'path' => $path
                                                     ])
                ]
            ]);

            return $response->getBody()->getContents();

        } catch (ClientException $e) {
            throw new Exception($e->getResponse()->getBody()->getContents());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function getFilenameFromPath($filePath)
    {
        return $this->forceStartingSlash(pathinfo($filePath, PATHINFO_BASENAME));
    }

    protected function getContents($filePath)
    {
        return file_get_contents($filePath);
    }
}
