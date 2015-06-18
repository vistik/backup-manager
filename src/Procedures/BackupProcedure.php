<?php namespace BackupManager\Procedures;

use BackupManager\Tasks;

/**
 * Class BackupProcedure
 * @package BackupManager\Procedures
 */
class BackupProcedure extends Procedure {

    /**
     * @param string $database
     * @param string $destination
     * @param string $destinationPath
     * @param string $compression
     * @param string $filename
     * @throws \BackupManager\Compressors\CompressorTypeNotSupported
     * @throws \BackupManager\Databases\DatabaseTypeNotSupported
     * @throws \BackupManager\Filesystems\FilesystemTypeNotSupported
     */
    public function run($database, $destination, $destinationPath, $compression, $filename) {
        $sequence = new Sequence;

        // begin the life of a new working file
        $localFilesystem = $this->filesystems->get('local');
        $workingFile = $this->getWorkingFile('local', $filename);

        // dump the database
        $sequence->add(new Tasks\Database\DumpDatabase(
            $this->databases->get($database),
            $workingFile,
            $this->shellProcessor
        ));

        // archive the dump
        $compressor = $this->compressors->get($compression);
        $sequence->add(new Tasks\Compression\CompressFile(
            $compressor,
            $workingFile,
            $this->shellProcessor
        ));

        $workingFileFull = $compressor->getCompressedPath($workingFile);
        $workingFile = basename($workingFileFull);

        // upload the archive
        $sequence->add(new Tasks\Storage\TransferFile(
            $localFilesystem, basename($workingFile),
            $this->filesystems->get($destination), $destinationPath . $workingFile
        ));

        // cleanup the local archive
        $sequence->add(new Tasks\Storage\DeleteFile(
            $localFilesystem,
            basename($workingFile)
        ));

        $sequence->execute();
    }
}
