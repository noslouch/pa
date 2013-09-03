<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Updater Transfer File
 *
 * @package         DevDemon_Updater
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com
 */
class Updater_transfer
{

    public $current_path = '';

    public function __construct()
    {
        // Creat EE Instance
        $this->EE =& get_instance();
        $this->EE->load->helper('file');
        $this->EE->load->helper('directory');
    }

    // ********************************************************************************* //

    public function init()
    {
        $settings =& $this->EE->updater->settings;
        $this->method = $settings['file_transfer_method'];
        $this->map = $settings['path_map'];

        if ($this->method == 'ftp') {
            $ftps = $settings['ftp'];

            if ($ftps['passive'] == 'yes') $ftps['passive'] = true;
            else $ftps['passive'] = false;

            if ($ftps['ssl'] == 'yes') $ftps['ssl'] = true;
            else $ftps['ssl'] = false;

            if (class_exists('Devdemon_ftp') === false) {
                require_once(dirname(dirname(__FILE__)).'/libraries/devdemon_ftp.php');
            }

            $this->FTP = new Devdemon_ftp($ftps);

            // Just in case
            if ($ftps['ssl'] == true) $this->FTP->ssl = true;

            if ( ! is_resource($this->FTP->conn_id)) {
                // FTP CONNECT
                if (!$this->FTP->connect()) {
                    throw new Exception($this->EE->lang->line('error:ftp:login'), 1);
                }
            }
        } elseif ($this->method == 'sftp') {
            $SFTPs = $settings['sftp'];

            set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)).'/libraries/phpseclib/');

            if (class_exists('Net_SFTP') === false) {
                require_once(dirname(dirname(__FILE__)).'/libraries/phpseclib/Net/SFTP.php');
            }

            $this->SFTP = new Net_SFTP($SFTPs['hostname'], $SFTPs['port']);

            if ( !($this->SFTP->bitmap & NET_SSH2_MASK_LOGIN) ) {
                $password = $SFTPs['password'];

                if (isset($SFTPs['auth_method']) === true && $SFTPs['auth_method'] == 'key') {
                    $key_pass = (isset($SFTPs['key_password']) === true) ? $SFTPs['key_password'] : '';
                    $password = new Crypt_RSA();
                    if ($key_pass) $password->setPassword($key_pass);

                    $key_contents = '';
                    if (isset($SFTPs['key_contents']) === true) {
                        $key_contents = $SFTPs['key_contents'];
                    }

                    if (isset($SFTPs['key_path']) === true && $SFTPs['key_path'] != false) {
                        if (file_exists($SFTPs['key_path']) === true) {
                            $key_contents = file_get_contents($SFTPs['key_path']);
                        }
                    }

                    $password->loadKey($key_contents);
                }

                if ($this->SFTP->login($SFTPs['username'], $password) != true) {
                    throw new Exception($this->EE->lang->line('error:sftp:login'), 1);
                }
            }
        }
    }

    // ********************************************************************************* //

    public function dir_list($path='')
    {
        if ($path == 'null') {
            $path = '';
        }

        $method = '_dir_list_'.$this->method;
        return $this->{$method}($path);
    }

    // ********************************************************************************* //

    private function _dir_list_local($path)
    {
        $items = array('files'=>array(), 'dirs'=>array() );

        // Empty? Then lets do ROOT
        if ($path == false) $path = FCPATH;

        // Make sure the last slash is always there
        $path = rtrim($path, '/\\').DIRECTORY_SEPARATOR;

        // Store it for reference later
        $this->current_path = $path;

        // Get the directory list
        $map = directory_map($path, 1, true);

        // Nothing returned? Shame
        if ($map == false) {
            $map = array();
        }

        foreach ($map as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }

            if (is_dir($path.$filename) === true) $items['dirs'][] = $filename;
            else $items['files'][] = $filename;
        }

        return $items;
    }

    // ********************************************************************************* //

    private function _dir_list_ftp($path)
    {
        $items = array('files'=>array(), 'dirs'=>array() );

        // Empty? Then lets do ROOT
        if ($path == false) {
            $path = @ftp_pwd($this->FTP->conn_id);

            if ($path === false) $path = '/';
        }

        // Make sure the last slash is always there
        $path = rtrim($path, '/\\').'/';

        // Store it for reference later
        $this->current_path = $path;

        // Get the raw list
        $dir_list = $this->FTP->list_files_raw($path);

        if ($dir_list == false) {
            return $items;
        }

        foreach ($dir_list as $name => $item) {
            if ($item['type'] == 'dir') $items['dirs'][] = $name;
            else $items['files'][] = $name;
        }

        return $items;
    }

    // ********************************************************************************* //

    private function _dir_list_sftp($path)
    {
        $items = array('files'=>array(), 'dirs'=>array() );

        // Empty? Then lets do ROOT
        if ($path == false) {
            $path = $this->SFTP->pwd();

            if ($path === false) $path = '/';
        }

        // Make sure the last slash is always there
        $path = rtrim($path, '/\\').'/';

        // Store it for reference later
        $this->current_path = $path;

        $dir_list = $this->SFTP->rawlist($path);

        if ($dir_list == false) {
            return $items;
        }

        foreach ($dir_list as $filename => $item) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }

            if ($item['type'] == NET_SFTP_TYPE_DIRECTORY) $items['dirs'][] = $filename;
            else $items['files'][] = $filename;
        }

        return $items;
    }

    // ********************************************************************************* //

    public function chdir($path='', $return_contents=false)
    {
        if ($path == 'null') {
            $path = '';
        }

        // Make sure the last slash is always there
        $path = rtrim($path, '/\\').'/';

        $method = '_chdir_'.$this->method;
        return $this->{$method}($path, $return_contents);
    }

    // ********************************************************************************* //

    private function _chdir_local($path, $return_contents)
    {
        $chdir = @scandir($path);

        if ($chdir === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:ftp:chdir_fail'), $path) , 1);
        }

        if ($return_contents) return $this->_dir_list_local($path);
        else return true;
    }

    // ********************************************************************************* //

    private function _chdir_ftp($path, $return_contents)
    {
        $chdir = @ftp_chdir($this->FTP->conn_id, $path);
        $path = ftp_pwd($this->FTP->conn_id);

        if ($chdir === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:ftp:chdir_fail'), $path) , 1);
        }

        if ($return_contents) return $this->_dir_list_ftp($path);
        else return true;
    }

    // ********************************************************************************* //

    private function _chdir_sftp($path, $return_contents)
    {
        $chdir = $this->SFTP->chdir($path);
        $pwd = $this->SFTP->pwd();

        if ($chdir === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:sftp:chdir_fail'), $path) , 1);
        }

        if ($return_contents) return $this->_dir_list_sftp($path);
        else return true;
    }

    // ********************************************************************************* //

    public function dir_exists($dest_dir, $name)
    {
        $dest_dir = $this->map[$dest_dir];

        $method = '_dir_exists_'.$this->method;
        return $this->{$method}($dest_dir, $name);
    }

    // ********************************************************************************* //

    private function _dir_exists_local($dest_dir, $name)
    {
        if (is_really_writable($dest_dir) === false) {

        }

        if (@is_dir($dest_dir.$name) === false) {
            return false;
        }

        return true;
    }

    // ********************************************************************************* //

    private function _dir_exists_ftp($dest_dir, $name)
    {
        if (!$this->FTP->changedir($dest_dir.$name)) {
            return false;
        }

        return true;
    }

    // ********************************************************************************* //

    private function _dir_exists_sftp($dest_dir, $name)
    {
        if ($this->SFTP->rawlist($dest_dir.$name) === false) {
            return false;
        }

        return true;
    }

    // ********************************************************************************* //

    public function mkdir($dest_dir, $name)
    {
        $dest_dir = $this->map[$dest_dir];

        $method = '_mkdir_'.$this->method;
        $this->{$method}($dest_dir, $name);
    }

    // ********************************************************************************* //

    private function _mkdir_local($dest_dir, $name)
    {
        if (is_really_writable($dest_dir) === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:local:not_writeable'), $dest_dir) , 1);
        }

        if (@is_dir($dest_dir.$name) === false) {
            if (@mkdir($dest_dir.$name, 0775, true) === false) {
                throw new Exception($this->EE->lang->line('error:local:mkdir_fail'), 1);
            }

            @chmod($dest_dir.$name, 0775);
        }

        return true;
    }

    // ********************************************************************************* //

    private function _mkdir_ftp($dest_dir, $name)
    {
        if (!$this->FTP->changedir($dest_dir)) {
            throw new Exception( sprintf($this->EE->lang->line('error:ftp:chdir_fail'), $dest_dir) , 1);
        }

        // Get dir array
        $arr = explode('/', $name);
        $temp = '';

        // Loop over all parts of the dir
        foreach ($arr as $tdir) {
            $temp .= $tdir.'/';

            // Does it already exist?
            if (!$this->FTP->changedir($dest_dir.$temp)) {
                // Create the dir
                if (!$this->FTP->mkdir($dest_dir.$temp)) {
                    throw new Exception( sprintf($this->EE->lang->line('error:ftp:mkdir_fail'), $dest_dir.$temp) , 1);
                }
            }
        }

        // Lets verify just to be sure!
        if (!$this->FTP->changedir($dest_dir.$name)) {
            throw new Exception( sprintf($this->EE->lang->line('error:ftp:after_mkdir_fail'), $dest_dir.$name) , 1);
        }

        return true;
    }

    // ********************************************************************************* //

    private function _mkdir_sftp($dest_dir, $name)
    {
        if ($this->SFTP->rawlist($dest_dir) === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:sftp:chdir_fail'), $dest_dir) , 1);
        }

        // Get dir array
        $arr = explode('/', $name);
        $temp = '';

        // Loop over all parts of the dir
        foreach ($arr as $tdir) {
            $temp .= $tdir.'/';

            // Does it already exist?
            if ($this->SFTP->rawlist($dest_dir.$temp) === false) {
                // Create the dir
                if (!$this->SFTP->mkdir($dest_dir.$temp)) {
                    throw new Exception( sprintf($this->EE->lang->line('error:sftp:mkdir_fail'), $dest_dir.$temp) , 1);
                }
            }
        }

        // Lets verify just to be sure!
        if (!$this->SFTP->chdir($dest_dir.$name)) {
            throw new Exception( sprintf($this->EE->lang->line('error:sftp:after_mkdir_fail'), $dest_dir.$name) , 1);
        }

        return true;
    }

    // ********************************************************************************* //

    public function upload($dest_dir, $source, $dest, $type='dir', $force_copy=false)
    {
        $dest_dir = $this->map[$dest_dir];

        // Remove those pesky \/ stuff
        $dest_dir = str_replace('\\/', '/', $dest_dir);
        $dest = str_replace('\\/', '/', $dest);
        $source = str_replace('\\/', '/', $source);

        $method = '_upload_'.$this->method;
        $this->{$method}($dest_dir, $source, $dest, $type, $force_copy);
    }

    // ********************************************************************************* //

    public function _upload_local($dest_dir, $source, $dest, $type, $force_copy)
    {
        if (is_really_writable($dest_dir) === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:local:not_writeable'), $dest_dir) , 1);
        }

        if ($type == 'file') {

            if (@copy($source, $dest_dir.$dest) === false) {
                throw new Exception( sprintf($this->EE->lang->line('error:local:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
            }

            return true;
        }



        if ($force_copy === true) {
            // Lets make sure the last slash is there!
            $dest = rtrim($dest, '\\/').DIRECTORY_SEPARATOR;
            $source = rtrim($source, '\\/').DIRECTORY_SEPARATOR;
            $dest_dir = rtrim($dest_dir, '\\/').DIRECTORY_SEPARATOR;

            if ($this->EE->debug_updater) $this->EE->firephp->log("LOCAL_DIR_COPY: {$source}  --  {$dest_dir}{$dest}");

            $this->recurse_copy($source, $dest_dir.$dest);
            return true;
        }

        if ($this->EE->debug_updater) $this->EE->firephp->log("LOCAL_DIR_COPY/RENAME: {$source}  --  {$dest_dir}{$dest}");

        if ($this->recurse_copy($source, $dest_dir.$dest) === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:local:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
        }

        return true;
    }

    // ********************************************************************************* //

    public function _upload_ftp($dest_dir, $source, $dest, $type)
    {
        if ($type == 'dir') {
            // Lets make sure the last slash is there!
            $dest = rtrim($dest, '\\/').'/';
            $source = rtrim($source, '\\/').'/';
            $dest_dir = rtrim($dest_dir, '\\/').'/';

            if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_DIR_MIRROR: {$source}  --  {$dest_dir}{$dest}");

            if (!$this->FTP->mirror($source, $dest_dir.$dest)) {
                throw new Exception( sprintf($this->EE->lang->line('error:ftp:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
            }
        } else {
            if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_FILE_UPLOAD: {$source}  --  {$dest_dir}{$dest}");

            if (!$this->FTP->upload($source, $dest_dir.$dest)) {
                throw new Exception( sprintf($this->EE->lang->line('error:ftp:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
            }
        }

        return true;
    }

    // ********************************************************************************* //

    public function _upload_sftp($dest_dir, $source, $dest, $type)
    {
        if ($type == 'dir') {
            $dest = rtrim($dest, '\\/').'/';
            $source = rtrim($source, '\\/').'/';
            $dest_dir = rtrim($dest_dir, '\\/').'/';

            if ($this->EE->debug_updater) $this->EE->firephp->log("SFTP_DIR_MIRROR: {$source}  --  {$dest_dir}{$dest}");

            if (!$this->sftp_mirror($source, $dest_dir.$dest)) {
                throw new Exception( sprintf($this->EE->lang->line('error:sftp:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
            }
        }
        else {
            if ($this->EE->debug_updater) $this->EE->firephp->log("SFTP_FILE_UPLOAD: {$source}  --  {$dest_dir}{$dest}");

            if (!$this->SFTP->put($dest_dir.$dest, $source, NET_SFTP_LOCAL_FILE)) {
                throw new Exception( sprintf($this->EE->lang->line('error:sftp:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
            }
        }

        return true;
    }

    // ********************************************************************************* //

    public function rename($dest_dir, $old, $new, $type='dir')
    {
        $dest_dir = $this->map[$dest_dir];

        $method = '_rename_'.$this->method;
        $this->{$method}($dest_dir, $old, $new, $type);
    }

    // ********************************************************************************* //

    public function _rename_local($dest_dir, $old, $new, $type)
    {
        if (is_really_writable($dest_dir) === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:local:not_writeable'), $dest_dir) , 1);
        }

        if (file_exists($dest_dir.$old) === false) return true;

        if ($this->EE->debug_updater) $this->EE->firephp->log("LOCAL_RENAME: {$dest_dir}{$old} \n {$dest_dir}{$new}");

        if ($type == 'dir') {
            @chmod($dest_dir.$old);
        }

        if (@rename($dest_dir.$old, $dest_dir.$new) === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:local:rename_fail'), $dest_dir.$old, $dest_dir.$new) , 1);
        }

        return true;
    }

    // ********************************************************************************* //

    public function _rename_ftp($dest_dir, $old, $new, $type)
    {
        if ($type == 'dir') {
            // Make sure we have a trailing slash
            //$dest_dir = preg_replace("/(.+?)\/*$/", "\\1/",  $dest_dir);
            //$old = preg_replace("/(.+?)\/*$/", "\\1/",  $old);
            //$new = preg_replace("/(.+?)\/*$/", "\\1/",  $new);

            if ($this->FTP->changedir($dest_dir.$old) === false) {
                return true;
            }

            // CHMOD
            $this->FTP->chmod($dest_dir.$old, 0777);
        }

        if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_RENAME: {$dest_dir}{$old}  --  {$dest_dir}{$new}");

        if ($this->FTP->rename($dest_dir.$old, $dest_dir.$new) === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:ftp:rename_fail'), $dest_dir.$old, $dest_dir.$new) , 1);
        }

        return true;
    }

    // ********************************************************************************* //

    public function _rename_sftp($dest_dir, $old, $new, $type)
    {
        if ($type == 'dir') {
            // Make sure we have a trailing slash
            //$dest_dir = preg_replace("/(.+?)\/*$/", "\\1/",  $dest_dir);
            //$old = preg_replace("/(.+?)\/*$/", "\\1/",  $old);
            //$new = preg_replace("/(.+?)\/*$/", "\\1/",  $new);

            if ($this->SFTP->rawlist($dest_dir.$old) === false) {
                return true;
            }

            // CHMOD
            $this->SFTP->chmod(0777, $dest_dir.$old);
        }

        if ($this->EE->debug_updater) $this->EE->firephp->log("SFTP_RENAME: {$dest_dir}{$old}  --  {$dest_dir}{$new}");

        if ($this->SFTP->rename($dest_dir.$old, $dest_dir.$new) === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:sftp:rename_fail'), $dest_dir.$old, $dest_dir.$new) , 1);
        }

        return true;
    }

    // ********************************************************************************* //

    public function delete($dest_dir, $dest, $type='dir')
    {
        $dest_dir = $this->map[$dest_dir];

        $method = '_delete_'.$this->method;
        $this->{$method}($dest_dir, $dest, $type);
    }

    // ********************************************************************************* //

    public function _delete_local($dest_dir, $dest, $type)
    {
        if (is_really_writable($dest_dir) === false) {
            throw new Exception( sprintf($this->EE->lang->line('error:local:not_writeable'), $dest_dir) , 1);
        }

        if (file_exists($dest_dir.$dest) == false) return true;

        if ($type == 'dir') {
            if ($this->EE->debug_updater) $this->EE->firephp->log("LOCAL_DIR_DELETE: {$dest_dir}{$dest}");

            @chmod($dest_dir.$dest, 0777);

            delete_files($dest_dir.$dest, true);

            if (@rmdir($dest_dir.$dest) === false) {
                throw new Exception( sprintf($this->EE->lang->line('error:local:delete_fail'), $type, $dest_dir.$dest) , 1);
            }
        } else {
            if ($this->EE->debug_updater) $this->EE->firephp->log("LOCAL_FILE_DELETE: {$dest_dir}{$dest}");

            if (@unlink($dest_dir.$dest) === false) {
                throw new Exception( sprintf($this->EE->lang->line('error:local:delete_fail'), $type, $dest_dir.$dest) , 1);
            }
        }

        return true;
    }

    // ********************************************************************************* //

    public function _delete_ftp($dest_dir, $dest, $type)
    {
        if ($type == 'dir') {
            // Make sure we have a trailing slash
            $dest = rtrim($dest, '\\/').'/';
            $dest_dir = rtrim($dest_dir, '\\/').'/';

            if ($this->FTP->changedir($dest_dir.$dest) === false) {
                return true;
            }
        }

        if ($type == 'dir')
        {
            if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_DIR_DELETE: {$dest_dir}{$dest}");

            // CHMOD
            $this->FTP->chmod($dest_dir.$dest, 0777);

            if ($this->FTP->delete_dir($dest_dir.$dest) === false) {
                throw new Exception( sprintf($this->EE->lang->line('error:ftp:delete_fail'), $type, $dest_dir.$dest) , 1);
            }
        } else {
            if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_FILE_DELETE: {$dest_dir}{$dest}");

            if ($this->FTP->delete_file($dest_dir.$dest) === false) {
                throw new Exception( sprintf($this->EE->lang->line('error:ftp:delete_fail'), $type, $dest_dir.$dest) , 1);
            }
        }

        return true;
    }

    // ********************************************************************************* //

    public function _delete_sftp($dest_dir, $dest, $type)
    {
        if ($type == 'dir') {
            // Make sure we have a trailing slash
            $dest = rtrim($dest, '\\/').'/';
            $dest_dir = rtrim($dest_dir, '\\/').'/';

            if ($this->SFTP->rawlist($dest_dir.$dest) === false)
            {
                return true;
            }
        }

        if ($type == 'dir') {
            if ($this->EE->debug_updater) $this->EE->firephp->log("SFTP_DIR_DELETE: {$dest_dir}{$dest}");

            // CHMOD
            $this->SFTP->chmod(0777, $dest_dir.$dest);

            if ($this->sftp_delete_dir($dest_dir.$dest) === false) {
                throw new Exception( sprintf($this->EE->lang->line('error:sftp:delete_fail'), $type, $dest_dir.$dest) , 1);
            }
        } else {
            if ($this->EE->debug_updater) $this->EE->firephp->log("SFTP_FILE_DELETE: {$dest_dir}{$dest}");

            if ($this->SFTP->delete($dest_dir.$dest) === false) {
                throw new Exception( sprintf($this->EE->lang->line('error:sftp:delete_fail'), $type, $dest_dir.$dest) , 1);
            }
        }

        return true;
    }

    // ********************************************************************************* //

    /**
     * Read a directory and recreate it remotely
     *
     * This function recursively reads a folder and everything it contains (including
     * sub-folders) and creates a mirror via FTP based on it.  Whatever the directory structure
     * of the original file path will be recreated on the server.
     *
     * @access  public
     * @param   string  path to source with trailing slash
     * @param   string  path to destination - include the base folder with trailing slash
     * @return  bool
     */
    private function sftp_mirror($locpath, $rempath)
    {
        if ( ! isset($this->SFTP)) {
            return false;
        }

        // Add a trailing slash to the file path if needed
        $locpath = rtrim($locpath, '\\/').'/';

        // Open the local file path
        if ($fp = @opendir($locpath)) {
            // Attempt to open the remote file path.
            if ($this->SFTP->rawlist($rempath) === false) {
                // If it doesn't exist we'll attempt to create the direcotory
                if ( ! $this->SFTP->mkdir($rempath) OR $this->SFTP->rawlist($rempath) === false) {
                    return false;
                }
            }

            // Recursively read the local directory
            while (false !== ($file = readdir($fp))) {
                if (@is_dir($locpath.$file) && substr($file, 0, 1) != '.') {
                    $this->sftp_mirror($locpath.$file."/", $rempath.$file."/");
                } elseif (substr($file, 0, 1) != ".") {
                    $this->SFTP->put($rempath.$file, $locpath.$file, NET_SFTP_LOCAL_FILE);
                }
            }
            return true;
        }

        return false;
    }

    // ********************************************************************************* //

    private function sftp_delete_dir($filepath)
    {
        if ( ! isset($this->SFTP)) {
            return false;
        }

        // Add a trailing slash to the file path if needed
        $filepath = rtrim($filepath, '\\/').'/';

        $list = $this->SFTP->nlist($filepath);

        if ($list != false OR count($list) > 0) {
            foreach ($list as $item) {
                if ($item == '.' OR $item == '..') continue;

                // If we can't delete the item it's probaly a folder so
                // we'll recursively call delete_dir()
                if ( ! @$this->SFTP->delete($filepath.$item)) {
                    $this->sftp_delete_dir($filepath.$item.'/');
                }
            }
        }

        $result = $this->SFTP->rmdir($filepath);

        if ($result === false) {
            return false;
        }

        return true;
    }

    // ********************************************************************************* //

    private function recurse_copy($source, $dest)
    {
        $source = rtrim($source, '/\\');
        $dest = rtrim($dest, '/\\');

        if (is_dir($source)) {
            $dir_handle=opendir($source);

            while($file=readdir($dir_handle))
            {
                if ($file != '.' && $file != '..') {
                    if (is_dir($source.'/'.$file)) {
                        @mkdir($dest.'/'.$file);
                        $this->recurse_copy($source.'/'.$file, $dest.'/'.$file);
                    } else {
                        //$this->EE->firephp->log("LOCAL_COPY: {$source}/{$file}  --  {$dest}/{$file}");
                        copy($source.'/'.$file, $dest.'/'.$file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            //$this->EE->firephp->log("LOCAL_COPY: {$source}  --  {$dest}");
            copy($source, $dest);
        }

    }

    // ********************************************************************************* //

} // END CLASS

/* End of file updater_transfer.php  */
/* Location: ./system/expressionengine/third_party/updater/libraries/updater_transfer.php */
