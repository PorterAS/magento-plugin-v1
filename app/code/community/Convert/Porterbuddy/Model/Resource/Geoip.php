<?php

class Convert_Porterbuddy_Model_Resource_Geoip
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    protected $localDir = 'geoip';
    protected $localFileName = 'GeoLite2-City.mmdb';
    protected $localArchiveName = 'GeoLite2-City.tar.gz';

    protected $localFile;
    protected $localArchive;
    protected $remoteArchive;

    public function __construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
        $this->localFile = $this->getAbsoluteDirectoryPath() . '/' . $this->localDir . '/' . $this->localFileName;
        $this->localArchive = $this->getAbsoluteDirectoryPath() . '/' . $this->localDir . '/' . $this->localArchiveName;
        $this->remoteArchive = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz';
    }

    public function getArchivePath()
    {
        return $this->localArchive;
    }

    public function getDBPath()
    {
        return $this->localFile;
    }

    public function getAbsoluteDirectoryPath()
    {
        return Mage::getBaseDir('var');
    }

    /**
     * @throws Convert_Porterbuddy_Exception
     */
    public function checkFilePermissions()
    {
        $relativeDirPath = 'var';

        $dir = $this->getAbsoluteDirectoryPath() . '/' . $this->localDir;
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new Convert_Porterbuddy_Exception($this->helper->__(
                    '%s exists but it is file, not dir.',
                    "$relativeDirPath/{$this->localDir}"
                ));
            } elseif ((!file_exists($this->localFile) || !file_exists($this->localArchive)) && !is_writable($dir)) {
                throw new Convert_Porterbuddy_Exception($this->helper->__(
                    '%s exists but files are not and directory is not writable.',
                    "$relativeDirPath/{$this->localDir}"
                ));
            } elseif (file_exists($this->localFile) && !is_writable($this->localFile)) {
                throw new Convert_Porterbuddy_Exception($this->helper->__(
                    '%s is not writable.',
                    "$relativeDirPath/{$this->localDir}/$this->localFileName"
                ));
            } elseif (file_exists($this->localArchive) && !is_writable($this->localArchive)) {
                throw new Convert_Porterbuddy_Exception($this->helper->__(
                    '%s is not writable.',
                    "$relativeDirPath/{$this->localDir}/{$this->localArchiveName}"
                ));
            }
        } elseif (!@mkdir($dir)) {
            throw new Convert_Porterbuddy_Exception($this->helper->__(
                'Can\'t create %s directory.',
                "$relativeDirPath/{$this->localDir}"
            ));
        }
    }

    /**
     * @return array
     * @throws Convert_Porterbuddy_Exception
     */
    public function update()
    {
        $this->checkFilePermissions();

        $remoteFileSize = $this->getSize($this->remoteArchive);
        if ($remoteFileSize < 100) {
            throw new Convert_Porterbuddy_Exception($this->helper->__('You are banned from downloading the file. Please try again in several hours.'));
        }

        /** @var $_session Mage_Core_Model_Session */
        $_session = Mage::getSingleton('core/session');
        $_session->setData('_geoip_file_size', $remoteFileSize);

        $src = fopen($this->remoteArchive, 'r');
        $target = fopen($this->localArchive, 'w');
        stream_copy_to_stream($src, $target);
        fclose($target);

        if (!filesize($this->localArchive)) {
            throw new Convert_Porterbuddy_Exception($this->helper->__('Download failed.'));
        }

        $this->unGZip($this->localArchive, $this->localFile);

        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        return array(
            'date' => Mage::app()->getLocale()->date(filemtime($this->localFile))->toString($format),
        );
    }

    public function getDatFileDownloadDate()
    {
        return file_exists($this->localFile) ? filemtime($this->localFile) : 0;
    }

    /**
     * Get size of remote file
     *
     * @param $file
     * @return mixed
     */
    public function getSize($file)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        return curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    }

    /**
     * Extracts single gzipped file. If archive will contain more then one file you will got a mess.
     *
     * @param $archive
     * @param $destination
     * @return int
     * @throws Convert_Porterbuddy_Exception
     */
    public function unGZip($archive, $destination)
    {
        $io = new Varien_Io_File();

        // GeoLite2-City.tar.gz contains GeoLite2-City_20180501/GeoLite2-City.mmdb, extract mmdb file
        $dir = $this->getAbsoluteDirectoryPath() . '/' . $this->localDir . '/extract';

        $phar = new PharData($archive, Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME);
        $result = $phar->extractTo($dir, null, true);
        if (!$result) {
            throw new Convert_Porterbuddy_Exception($this->helper->__('Cannot extract geoip archive'));
        }

        /** @var SplFileInfo|null $dbFile */
        $dbFile = null;
        $objects = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($objects as $name => $object) {
            if ($object->isFile() && $this->localFileName == $object->getFilename()) {
                $dbFile = $object;
                break;
            }
        }
        if (!$dbFile) {
            throw new Convert_Porterbuddy_Exception($this->helper->__('Cannot find database in geoip archive'));
        }

        // move file to target location
        $io->rm($destination);

        if (!$io->mv($dbFile->getPathname(), $destination)) {
            throw new Convert_Porterbuddy_Exception($this->helper->__('Cannot move geoip extracted database'));
        }

        // remove download directory
        $io->rmdir($dir, true);

        return filesize($destination);
    }
}
