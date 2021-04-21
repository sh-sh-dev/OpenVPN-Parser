<?php

namespace Shay3gan\OpenVPN;

use InvalidArgumentException;

/**
 * Class Parser
 * @package Shay3gan\OpenVPN\Parser
 */
class Parser
{
    /**
     * @var string status file path
     */
    private $statusFile = null;

    /**
     * Get current passed status file path
     *
     * @return string|null
     */
    public function getStatusFile()
    {
        return $this->statusFile;
    }

    /**
     * Set status file path
     *
     * @param string $statusFilePath
     * @return void
     */
    public function setStatusFile($statusFilePath)
    {
        $this->statusFile = $statusFilePath;
    }

    /**
     * Parse status file
     *
     * @return array
     */
    public function parse()
    {
        $statusFile = $this->getStatusFile();
        $this->validateStatusFile($statusFile);

        $fileHandle = fopen($statusFile, 'r');
        $result = [
            'updated_at' => null,
            'connections' => null,
        ];

        while (!feof($fileHandle)) {
            $readLine = fgets($fileHandle, 4096);
            $line = explode(',', $readLine);

            switch ($line[0]) {
                case 'TIME':
                    $result['updated_at'] = trim($line[2]);
                    break;

                case 'CLIENT_LIST':
                    // Empty virtual-address means user is not authenticated yet
                    if (empty($line[3]))
                        continue 2;

                    $result['connections'][$line[3]] = [
                        'common_name'      =>    $line[1] === 'UNDEF' ? null : $line[1],
                        'origin'           =>    $line[2],
                        'virtual_address'  =>    $line[3],
                        'received'         =>    $line[5],
                        'sent'             =>    $line[6],
                        'username'         =>    $line[9] === 'UNDEF' ? null : $line[9],
                        'connection_id'    =>    $line[10],
                        'last_ref'         =>    null,
                        'created_at'       =>    $line[8],
                    ];
                    break;

                case 'ROUTING_TABLE':
                    // 'last ref' index is the latest index, so it has a NEWLINE marker and should be removed
                    $result['connections'][$line[1]]['last_ref'] = trim($line[5]);
                    break;

                default:
                    continue 2;
            }
        }

        fclose($fileHandle);

        return $result;
    }

    /**
     * Check entered status file
     *
     * @param string $statusFile
     * @return bool
     */
    private function validateStatusFile($statusFile) {
        if (!$statusFile)
            throw new InvalidArgumentException('Status file is not passed.');
        if (!file_exists($statusFile))
            throw new InvalidArgumentException('File path is invalid.');

        return true;
    }
}
