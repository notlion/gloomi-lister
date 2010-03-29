<?php
/***********************************************************
* filename     functions.lib.php
* description  Supporting functions for zipcreate.cls.php,
*              zipextract.cls.php
* project
* author       redmonkey
* version      0.1
* status       beta
* support      <!-- e --><a href="mailto:zipclass@redmonkey.com">zipclass@redmonkey.com</a><!-- e -->
* license      GPL
*
* file history
* ============
* 01/01/2005   v0.1 initial version
************************************************************/

/**
* @return int             DOS date and time
* @param  int _timestamp  Unix timestamp
* @desc                   returns DOS date and time of the timestamp or
*                         current local time if no timestamp is given
*/
function unix2dostime($_timestamp = 0)
{
  $timebit = ($_timestamp == 0) ? getdate() : getdate($_timestamp);

  if ($timebit['year'] < 1980)
  {
    return (1 << 21 | 1 << 16);
  }

  $timebit['year'] -= 1980;

  return ($timebit['year']    << 25 | $timebit['mon']     << 21 |
          $timebit['mday']    << 16 | $timebit['hours']   << 11 |
          $timebit['minutes'] << 5  | $timebit['seconds'] >> 1);
}

/**
* @return int           Unix timestamp
* @param  int _dostime  DOS date and time
* @desc                 converts a DOS date and time integer to a Unix
*                       timestamp
*/
function dos2unixtime($_dostime)
{
  $sec  = 2 * ($_dostime     & 0x1f);
  $min  = ($_dostime >> 5)   & 0x3f;
  $hrs  = ($_dostime >> 11)  & 0x1f;
  $day  = ($_dostime >> 16)  & 0x1f;
  $mon  = ($_dostime >> 21)  & 0x0f;
  $year = (($_dostime >> 25) & 0x7f) + 1980;

  return mktime($hrs, $min, $sec, $mon, $day, $year);
}

/**
* @return bool               true on sccuess false on failure
* @param  string  _path      path of directories to create
* @param  int     _modtime   timestamp to set last modified time of directory
* @param  string  _dir       base directory to start creating directories in
* @desc                      loops through the individual directories in $_path
*                            and attempts to create any that do not exist
*/
function make_dirs($_path, $_modtime = false, $_dir = '.')
{
  if ($_path == './')
  {
    return true;
  }

  $_dir     = $_dir == '/' ? '' : $_dir;

  $_modtime = !is_integer($_modtime) ? time() : $_modtime;

  $dirs     = explode('/', $_path);

  for ($i = 0, $n_dirs = count($dirs); $i < $n_dirs; $i++)
  {
    $_dir = $_dir . '/' . $dirs[$i];
    if (!is_dir($_dir))
    {
      if(!@mkdir($_dir, 0755))
      {
        return false;
      }
    }

    if ($i == ($n_dirs -1))
    {
      // supress errors as this does not work on win32 platforms
      @touch($_dir, $_modtime);
    }

  }
  return true;
}

/**
* @return int                position of last occurrence of _needle in
*                            _haystack
* @param  string  _haystack  string to be searched
* @param  string  _needle    search string
* @param  int     _offset    position to start search
* @desc                      find position of last occurrence of a string
*                            within a string
*/
function _strrpos($_haystack, $_needle, $_offset = 0)
{
  if ((int) array_shift(explode('.', phpversion())) > 4)
  {
    return strrpos($_haystack, $_needle, $_offset);
  }

  $_haystack = $_offset < 0 ? substr($_haystack, 0, $_offset)
                            : substr($_haystack, $_offset);

  $pos = strpos(strrev($_haystack), strrev($_needle));

  if ($pos !== false)
  {
    $pos = strlen($_haystack) - strlen($_needle) - $pos;

    return $_offset > 0 ? $pos + $_offset : $pos;
  }
  return false;
}
?>