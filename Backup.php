<?php
namespace FreePBX\modules\Cel;
use Symfony\Component\Process\Process;
use FreePBX\modules\Backup as Base;
use Symfony\Component\Filesystem\Filesystem;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $fs = new Filesystem();
    $tmpdir = $this->FreePBX->Config->get('ASTSPOOLDIR').'/dbdump';
    $cdrname = $this->FreePBX->Config->get('CDRDBNAME');
    $cdrhost = $this->FreePBX->Config->get('CDRDBHOST');
    $cdruser = $this->FreePBX->Config->get('CDRDBUSER');
    $cdrpass = $this->FreePBX->Config->get('CDRDBPASS');
    $cdrport = $this->FreePBX->Config->get('CDRDBPORT');
    $command = ['/usr/bin/mysqldump'];
    if(!empty($cdrhost)){
        $command[] = '--host';
        $command[] = $cdrhost;
    }
    if(!empty($cdrport)){
        $command[] = '--port';
        $command[] = $cdrport;
    }
    if(!empty($cdruser)){
        $command[] = '--user';
        $command[] = $cdruser;
    }
    if(!empty($cdrpass)){
        $command[] = '--password';
        $command[] = $cdrpass;
    }
    $command[] = $cdrname;
    $command[] = '--opt';
    $command[] = '--compact';
    $command[] = '--table';
    $command[] = 'cel';
    $command[] = '--skip-lock-tables';
    $command[] = '--no-create-info';
    $command[] = '|';
    $command[] = '/usr/bin/gzip';
    $command[] = '>';
    $command[] = $tmpdir.'/cel.sql.gz';
    $command = (implode(" ", $command));
    $process= new Process($command);
    if($fs->exists($tmpdir)){
        $fs->remove([$tmpdir]);
    }
    mkdir($tmpdir, 0755, true);
    $dirs = [$tmpdir];
    //$process->disableOutput();
    $process->mustRun();
    $fileObj = new \SplFileInfo($tmpdir . '/cel.sql.gz');
    $this->addFile($fileObj->getBasename(), $fileObj->getPath(), '', $fileObj->getExtension());
    $this->addDirectories([$fileObj->getPath()]);
    $this->addDependency('cdr');
  }
}