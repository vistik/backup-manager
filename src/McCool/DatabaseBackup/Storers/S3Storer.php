<?php namespace McCool\DatabaseBackup\Storers;

use Aws\Common\Aws;

class S3Storer implements StorerInterface
{
    /**
     * The AWS S3 client instance.
     *
     * @var string
     */
    private $s3Client;

    /**
     * The AWS S3 bucket.
     *
     * @var string
     */
    private $bucket;

    /**
     * The AWS S3 path.
     *
     * @var string
     */
    private $s3Path;

    /**
     * The backup filename.
     *
     * @var string
     */
    private $filename;

    /**
     * Initialize the S3Storer instance.
     *
     * @param  string  $s3Client
     * @param  string  $bucket
     * @param  string  $s3Path
     * @return self
     */
    public function __construct($s3Client, $bucket, $s3Path)
    {
        $this->s3Client = $s3Client;
        $this->bucket   = $bucket;
        $this->s3Path   = $s3Path;
    }

    /**
     * Sets the filename for the backup.
     *
     * @param  string  $filename
     * @return void
     */
    public function setInputFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Stores the backup to the given storage provider.
     *
     * @return void
     */
    public function store()
    {
        $this->s3Client->putObject([
            'Bucket'     => $this->bucket,
            'Key'        => $this->getS3Path() . $this->getFilename(),
            'SourceFile' => $this->filename,
            'ACL'        => 'private',
        ]);
    }

    /**
     * Returns the S3 path.
     *
     * @return string
     */
    private function getS3Path()
    {
        if ( ! preg_match("/\/$/", $this->s3Path)) {
            return $this->s3Path . '/';
        }

        return $this->s3Path;
    }

    /**
     * Returns the base backup filename.
     *
     * @return string
     */
    private function getFilename()
    {
        return basename($this->filename);
    }
}