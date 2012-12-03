<?php
/*
Copyright (c) 2012 Sebastián Grignoli

Permission is hereby granted, free of charge, to any
person obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the
Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice
shall be included in all copies or substantial portions of
the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

$server = new WebDAV();
$server->dispatch();


class WebDAV {

  public function __construct($root = NULL){
    if(is_null($root)) $root = dirname(dirname(__FILE__));
    $this->root = $root; 
  }

  protected function get()
  {
    echo(file_get_contents($this->resource()));
  }

  protected function put()
  {
    file_put_contents($this->resource(), $this->payload);
  }

  protected function delete()
  {
    if(is_dir($this->resource())){
      $this->recursiveRemoveDirectory($this->resource(), 0);      
    } else {
      unlink($this->resource());
    }
  }

  protected function move()
  {
    die("Move!");
  }

  protected function mkcol()
  {
    mkdir($this->resource());
    chmod($this->resource(), +0777);
  }

  protected function propfind()
  {
    $resp = 
'<D:multistatus xmlns:D="DAV:">
  '.$this->listCollection($this->resource()).'
</D:multistatus>';
   header('HTTP/1.1 207 Multi-Status');
   echo($resp);
  }

  public function dispatch()
  {
    $this->server = $_SERVER;
    $this->request = $_REQUEST;
    $this->payload = stream_get_contents(fopen('php://input','r+'));

    /*  //remove this line to enable some logging
    @file_put_contents("lastrequest.txt", 
        "\n\n\n=============\n\n\n\$resource = ".$this->resource().
        "\n\$server = ".var_export($this->server,1).
        ";\n\$request = ".var_export($this->request,1).
        ";\n\$payload = ".var_export($this->payload,1).";",
      FILE_APPEND);
    /**/

    switch(strtoupper($this->server['REQUEST_METHOD'])) {
      case 'PROPFIND':
        $this->propfind(); break;
      case 'GET':
        $this->get(); break;
      case 'PUT':
        $this->put(); break;
      case 'DELETE':
        $this->delete(); break;
      case 'MOVE':
        $this->move(); break;
      case 'MKCOL':
        $this->mkcol(); break;
    }
  }

  protected function resource()
  {
    $res = $this->server['REQUEST_URI'];
    
    if(substr($res,0,strlen($this->server['SCRIPT_NAME'])) == $this->server['SCRIPT_NAME']) {
      $res = substr($res, strlen($this->server['SCRIPT_NAME'])); 
    }

    $res = $this->root . $res;

    $res = str_replace('//', '/', $res);

    $res = urldecode($res);

    return $res;
  }

  protected function listCollection($res)
  {
    $o = "";
    if ($h = opendir($dir = $this->removeTrailingSlash($res))) {
    while (false !== ($entry = readdir($h))) {
        if ($entry != "." && $entry != "..") {
          $data = stat($dir.'/'.$entry);
          if(is_dir($dir.'/'.$entry))
            {
            $o .= 
'  <D:response xmlns:lp1="DAV:" xmlns:g0="DAV:">
    <D:href>'.$this->removeTrailingSlash($this->server['REQUEST_URI']).'/'.$entry.'</D:href>
    <D:propstat>
      <D:prop>
        <lp1:resourcetype>
          <D:collection/>
        </lp1:resourcetype>
        <lp1:getlastmodified>'.date('D, j M Y H:i:s \G\M\T', $data['mtime']).'</lp1:getlastmodified>
        <lp1:creationdate>'.date('Y-m-d\TH:i:s\Z', $data['ctime']).'</lp1:creationdate>
      </D:prop>
      <D:status>HTTP/1.1 200 OK</D:status>
    </D:propstat>
    <D:propstat>
      <D:prop>
        <g0:getcontentlength/>
      </D:prop>
      <D:status>HTTP/1.1 404 Not Found</D:status>
    </D:propstat>
  </D:response>
';
          } else {
            $o .= 
'  <D:response xmlns:lp1="DAV:" xmlns:g0="DAV:">
    <D:href>'.$this->removeTrailingSlash($this->server['REQUEST_URI']).'/'.$entry.'</D:href>
    <D:propstat>
      <D:prop>
        <lp1:getlastmodified>'.date('D, j M Y H:i:s \G\M\T', $data['mtime']).'</lp1:getlastmodified>
        <lp1:creationdate>'.date('Y-m-d\TH:i:s\Z', $data['ctime']).'</lp1:creationdate>
        <lp1:getcontentlength>'.$data['size'].'</lp1:getcontentlength>
        <lp1:resourcetype/>
      </D:prop>
      <D:status>HTTP/1.1 200 OK</D:status>
    </D:propstat>
  </D:response>
';
          }
        }
    }
    closedir($h);
    };
    return $o;
  }

  protected function removeTrailingSlash($path){
    if(substr($path,-1) == "/") {
      return substr($path, 0, -1);
    } else {
      return $path;
    }
  }

  protected function recursiveRemoveDirectory($directory, $empty=FALSE)
  {
    if(substr($directory,-1) == '/')
    {
      $directory = substr($directory,0,-1);
    }

    if(!file_exists($directory) || !is_dir($directory))
    {
      return FALSE;
    }elseif(!is_readable($directory))
    {
      return FALSE;
    }else{
      $handle = opendir($directory);

      while (FALSE !== ($item = readdir($handle)))
      {
        if($item != '.' && $item != '..')
        {
          $path = $directory.'/'.$item;

          if(is_dir($path)) 
          {
            $this->RecursiveRemoveDirectory($path);
          }else{
            unlink($path);
          }
        }
      }
      closedir($handle);

      if($empty == FALSE)
      {
        if(!rmdir($directory))
        {
          return FALSE;
        }
      }
      return TRUE;
    }
  }
    
}
