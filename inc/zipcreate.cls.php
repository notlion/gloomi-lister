<?php
/***********************************************************
* filename     zipcreate.cls.php
* description  Create zip files on the fly
* project
* author       redmonkey
* version      0.3
* status       beta
* support      <!-- e --><a href="mailto:zipclass@redmonkey.com">zipclass@redmonkey.com</a><!-- e -->
* license      GPL
*
* depends      function unix2dostime() (found in supporting
*              function library (includes/functions.lib.php))
*
* notes        zip file format can be found at
*              <!-- m --><a class="postlink" href="http://www.pkware.com/company/standards/appnote/">http://www.pkware.com/company/standards/appnote/</a><!-- m -->
*
* notes        the documented zip file format omits to detail
*              the required header signature for the data
*              descriptor (extended local file header) section
*              which is (0x08074b50). while many decompression
*              utilities will ignore this error, this signature
*              is vital for compatability with Stuffit Expander
*              for Mac if you have included the data descriptor
*
* notes        while using bzip2 compression offers a reduced
*              file size it does come at the expense of higher
*              system resources usage. the decompression
*              utility will also have to be compatabile with
*              at least v4.6 zip file format specification
*
* file history
* ============
* 01/01/2005   v0.1 initial version
* 02/01/2005   v0.2 added checking and handling for files of
*                   zero bytes in length
* 30/03/2010   v0.3 added support for streaming zip's (author: Darklord <darklord [ät] darkboxed.org>)
************************************************************/
class ZipCreate
{
  var $filedata; // file data
  var $cntrldir; // central directory record
  var $comment;  // zip file comment
  var $offset;   // local header offset tracker
  var $entries;  // counter for total entries within the zip
  var $ztype;    // current compression type

  var $stream;
  var $stream_filename;
  var $headers_sent;

  /**
  * @return
  * @param   string _ztype  compression type to use, currently only supporting
  *                         gzip (deflated), bzip2, and store (no compression)
  * @desc                   constructor, initialise class variables and set compression
  *                         type (defaults to gzip (Deflated)) for files
  */
  function ZipCreate($_ztype = 'gzip', $_stream = false, $_strem_filename = "")
  {
    $this->filedata = '';
    $this->cntrldir = '';
    $this->comment  = '';
    $this->offset   = 0;
    $this->entries  = 0;

    $this->stream = $_stream;
    $this->strem_filename = $_strem_filename;
    $this->headers_sent = false;

    switch(strtolower($_ztype))
    {
      case 'gzip' :
        if (!function_exists('gzcompress'))
        {
          trigger_error('Your PHP installation does not support gzip compression', E_USER_ERROR);
        }

        $this->ztype = 'gzip';
        break;

      case 'bzip2':
        if (!function_exists('bzcompress'))
        {
          trigger_error('Your PHP installation does not support bzip2 compression', E_USER_ERROR);
        }

        $this->ztype = 'bzip2';
        break;

      case 'stored':
        $this->ztype = 'store';
        break;

      default      :
        // default to no (Stored) compression type for anything else
        $notice_msg  = 'Unsupported compression type (' . $_ztype . ') using Stored instead';
        $this->ztype = 'store';
        trigger_error($notice_msg, E_USER_NOTICE);
    }
  }

  /**
  * @return
  * @param  string  _path       directory path
  * @param  string  _timestamp  unix timestamp for dir last modified date and time
  * @desc                       adds a directory record to the archive
  */
  function add_dir($_path, $_timestamp = 0)
  {
    return $this->add_file(null, $_path, $_timestamp);
  }

  /**
  * @return
  * @param  string  _data       file contents
  * @param  string  _name       name of the file within the archive including path
  * @param  int     _timestamp  unix timestamp for file last modified date and time
  * @desc                       adds a file to the archive
  */
  function add_file($_data = null, $_name, $_timestamp = 0)
  {
    if (is_null($_data))                // assume it's a directory
    {
      $z_type = 'store';                // set compression to none
      $ext_fa = 0x10;                   // external file attributes
      $_data  = '';                     // initialise $_data
    }
    elseif ($_data == '')               // assume a zero byte length file
    {
      $z_type = 'store';                // set compression to none
      $ext_fa = 0x20;                   // external file attributes
    }
    else                                // assume it's a file
    {
      $z_type = $this->ztype;
      $ext_fa = 0x20;                   // external file attributes
    }

    // remove leading and trailing spaces from filename
    // and correct any erros with directory seperators
    $_name    = trim(str_replace('\\\'', '/', $_name));

    // remove any invalid path definitions
    $_name    = preg_replace('/^([A-z]:\/+|\.?\/+)/', '', $_name);

    // set last modified time of file in required DOS format
    $mod_time = unix2dostime($_timestamp);

    switch($z_type)
    {
      case  'gzip':
        $min_ver = 0x14;                   // minimum version needed to extract (2.0)
        $zmethod = 0x08;                   // compression method
        $c_data  = gzcompress($_data);     // compress file
        $c_data  = substr($c_data, 2, -4); // fix crc bug
        break;

      case 'bzip2':
        $min_ver = 0x2e;                   // minimum version needed to extract (4.6)
        $zmethod = 0x0c;                   // compression method
        $c_data  = bzcompress($_data);     // compress file
        break;

      default     :                        // default to stored (no) compression
        $min_ver = 0x0a;                   // minimum version needed to extract (1.0)
        $zmethod = 0x00;                   // compression method
        $c_data  = $_data;
        break;
    }


    // file details
    $crc32    = crc32($_data);         // crc32 checksum of file
    $c_len    = strlen($c_data);       // compressed length of file
    $uc_len   = strlen($_data);        // uncompressed length of file
    $fn_len   = strlen($_name);        // length of filename

    // pack file data
    $filedata = pack('VvvvVVVVvva' . $fn_len . 'a' . $c_len . 'VVVV',
                     0x04034b50,    // local file header signature      (4 bytes)
                     $min_ver,      // version needed to extract        (2 bytes)
                     0x08,          // gen purpose bit flag             (2 bytes)
                     $zmethod,      // compression method               (2 bytes)
                     $mod_time,     // last modified time and date      (4 bytes)
                     0,             // crc-32                           (4 bytes)
                     0,             // compressed filesize              (4 bytes)
                     0,             // uncompressed filesize            (4 bytes)
                     $fn_len,       // length of filename               (2 bytes)
                     0,             // extra field length               (2 bytes)

                     $_name,        // filename                 (variable length)
                     $c_data,       // compressed data          (variable length)
                     0x08074b50,    // extended local header signature  (4 bytes)
                     $crc32,        // crc-32                           (4 bytes)
                     $c_len,        // compressed filesize              (4 bytes)
                     $uc_len);      // uncompressed filesize            (4 bytes)

    if($this->stream)
    {
      // if we are going to stream the zip just send the filedata
      $this->send($filedata);
    }else
    {
      // otherwies just add it to filedata for later
      $this->filedata .= $filedata;
    }

    // pack file data and add to central directory
    $this->cntrldir .= pack('VvvvvVVVVvvvvvVVa' . $fn_len,
                             0x02014b50,     // central file header signature   (4 bytes)
                             0x14,           // version made by                 (2 bytes)
                             $min_ver,       // version needed to extract       (2 bytes)
                             0x08,           // gen purpose bit flag            (2 bytes)
                             $zmethod,       // compression method              (2 bytes)
                             $mod_time,      // last modified time and date     (4 bytes)
                             $crc32,         // crc32                           (4 bytes)
                             $c_len,         // compressed filesize             (4 bytes)
                             $uc_len,        // uncompressed filesize           (4 bytes)
                             $fn_len,        // length of filename              (2 bytes)
                             0,              // extra field length              (2 bytes)
                             0,              // file comment length             (2 bytes)
                             0,              // disk number start               (2 bytes)
                             0,              // internal file attributes        (2 bytes)
                             $ext_fa,        // external file attributes        (4 bytes)
                             $this->offset,  // relative offset of local header (4 bytes)
                             $_name);        // filename                (variable length)

    // update offset tracker
    $this->offset += strlen($filedata);

    // increment entry counter
    $this->entries++;

    // cleanup
    unset($c_data, $filedata, $ztype, $min_ver, $zmethod, $mod_time, $c_len, $uc_len, $fn_len);
  }

  /**
  * @return
  * @param  string  _comment  zip file comment
  * @desc                     adds a comment to the archive
  */
  function add_comment($_comment)
  {
    $this->comment = $_comment;
  }

  /**
  * @return string       the zipped file
  * @desc                throws everything together and returns it
  */
  function build_zip()
  {
    $this->send($this->filedata);
    $this->finish_stream();
  }

  /**
  * @desc                to finish streaming the zip throw the rest of the zip stuff in and send it
  */
  function finish_stream()
  {
    $com_len = strlen($this->comment);     // length of zip file comment
    $this->send( 
          $this->cntrldir                 // .zip central directory record (variable length)
        . pack('VvvvvVVva' . $com_len,
                0x06054b50,               // end of central dir signature          (4 bytes)
                0,                        // number of this disk                   (2 bytes)
                0,                        // number of the disk with start of
                                          // central directory record              (2 bytes)
                $this->entries,           // total # of entries on this disk       (2 bytes)
                $this->entries,           // total # of entries overall            (2 bytes)
                strlen($this->cntrldir),  // size of central dir                   (4 bytes)
                $this->offset,            // offset to start of central dir        (4 bytes)
                $com_len,                 // .zip file comment length              (2 bytes)
                $this->comment)           // .zip file comment             (variable length)
        );
  }

  /**
  * @return string       the data passed to the function
  * @desc                Send string, sending HTTP headers if necessary.
  */
  function send($data)
  {
    if (!$this->headers_sent)
    {
        $headers = array(
          'Content-Type'              => 'application/x-zip',
          'Content-Disposition'       => 'attachment' . "; filename=\"{$this->strem_filename}\"",
          'Pragma'                    => 'public',
          'Cache-Control'             => 'public, must-revalidate',
          'Content-Transfer-Encoding' => 'binary',
        );

        foreach ($headers as $key => $val)
          header("$key: $val");
    $this->headers_sent = true;
    }

    echo $data;
  }
}
?>