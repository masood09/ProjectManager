<?php
// Copyright (C) 2013 Masood Ahmed

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.

class Upload extends Phalcon\Mvc\Model
{
    public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    public function initialize()
    {
        $this->belongsTo('user_id', 'User', 'id');
        $this->belongsTo('project_id', 'Project', 'id');
        $this->belongsTo('task_id', 'Task', 'id');
        $this->belongsTo('comment_id', 'Comment', 'id');
    }

    public function getUrl()
    {
        return $this->project_id . '/' . $this->filename;
    }

    public function getSize()
    {
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return $this->size ? round($this->size/pow(1024, ($i = floor(log($this->size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    }

    public function isImage()
    {
        $extension = pathinfo($this->filename, PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        return in_array($extension, array('png', 'jpg', 'jpeg', 'gif'));
    }

    public function getIconFile()
    {
        $extension = pathinfo($this->filename, PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        switch ($extension) {
            case 'doc':
                return 'doc-icon-64x64.png';
            case 'docx':
                return 'docx-icon-64x64.png';
            case 'log':
                return 'log-icon-64x64.png';
            case 'msg':
                return 'msg-icon-64x64.png';
            case 'odt':
                return 'odt-icon-64x64.png';
            case 'pages':
                return 'pages-icon-64x64.png';
            case 'rtf':
                return 'rtf-icon-64x64.png';
            case 'tex':
                return 'tex-icon-64x64.png';
            case 'txt':
                return 'txt-icon-64x64.png';
            case 'wpd':
                return 'wpd-icon-64x64.png';
            case 'wps':
                return 'wps-icon-64x64.png';

            case 'csv':
                return 'csv-icon-64x64.png';
            case 'dat':
                return 'generic-icon-64x64.png';
            case 'gbr':
                return 'generic-icon-64x64.png';
            case 'ged':
                return 'ged-icon-64x64.png';
            case 'ibooks':
                return 'ibooks-icon-64x64.png';
            case 'key':
                return 'key-icon-64x64.png';
            case 'keychain':
                return 'generic-icon-64x64.png';
            case 'pps':
                return 'pps-icon-64x64.png';
            case 'ppt':
                return 'ppt-icon-64x64.png';
            case 'pptx':
                return 'pptx-icon-64x64.png';
            case 'sdf':
                return 'generic-icon-64x64.png';
            case 'tar':
                return 'tar-icon-64x64.png';
            case 'tax2012':
                return 'tax2012-icon-64x64.png';
            case 'vcf':
                return 'vcf-icon-64x64.png';
            case 'xml':
                return 'xml-icon-64x64.png';

            case 'aac':
            case 'aif':
            case 'iff':
            case 'm3u':
            case 'm4a':
            case 'mid':
            case 'mp3':
            case 'mpa':
            case 'ra':
            case 'wav':
            case 'wma':
                return 'mp3-icon-64x64.png';

            case '3g2':
            case '3gp':
            case 'asf':
            case 'asx':
            case 'avi':
            case 'mov':
            case 'mp4':
            case 'mpg':
            case 'vob':
            case 'mwv':
                return 'mp4-icon-64x64.png';
            case 'flv':
                return 'flv-icon-64x64.png';
            case 'rm':
                return 'rm-icon-64x64.png';
            case 'srt':
                return 'srt-icon-64x64.png';
            case 'swf':
                return 'swf-icon-64x64.png';

            case '3dm':
                return '3dm-icon-64x64.png';
            case '3ds':
                return '3ds-icon-64x64.png';
            case 'max':
                return 'max-icon-64x64.png';
            case 'obj':
                return 'obj-icon-64x64.png';

            case 'bmp':
            case 'gif':
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'thm':
            case 'tif':
            case 'tiff':
                return 'tiff-icon-64x64.png';
            case 'dds':
                return 'generic-icon-64x64.png';
            case 'psd':
                return 'psd-icon-64x64.png';
            case 'pspimage':
                return 'pspimage-icon-64x64.png';
            case 'tga':
                return 'tga-icon-64x64.png';
            case 'yuv':
                return 'generic-icon-64x64.png';

            case 'ai':
                return 'ai-icon-64x64.png';
            case 'eps':
                return 'eps-icon-64x64.png';
            case 'ps':
                return 'ps-icon-64x64.png';
            case 'svg':
                return 'svg-icon-64x64.png';

            case 'indd':
                return 'indd-icon-64x64.png';
            case 'pct':
                return 'pct-icon-64x64.png';
            case 'pdf':
                return 'pdf-icon-64x64.png';

            case 'xlr':
                return 'xlr-icon-64x64.png';
            case 'xls':
                return 'xls-icon-64x64.png';
            case 'xlsx':
                return 'xlsx-icon-64x64.png';

            case 'accdb':
                return 'accdb-icon-64x64.png';
            case 'db':
                return 'db-icon-64x64.png';
            case 'dbf':
                return 'dbf-icon-64x64.png';
            case 'mdb':
                return 'mdb-icon-64x64.png';
            case 'pdb':
                return 'generic-icon-64x64.png';
            case 'sql':
                return 'sql-icon-64x64.png';

            case 'apk':
                return 'generic-icon-64x64.png';
            case 'app':
                return 'app-icon-64x64.png';
            case 'bat':
                return 'bat-icon-64x64.png';
            case 'cgi':
                return 'cgi-icon-64x64.png';
            case 'com':
                return 'com-icon-64x64.png';
            case 'exe':
                return 'exe-icon-64x64.png';
            case 'gadget':
                return 'gadget-icon-64x64.png';
            case 'pif':
                return 'pif-icon-64x64.png';
            case 'jar':
                return 'jar-icon-64x64.png';
            case 'vb':
                return 'vb-icon-64x64.png';
            case 'wsf':
                return 'generic-icon-64x64.png';

            case 'dwg':
                return 'dwg-icon-64x64.png';
            case 'dxf':
                return 'dxf-icon-64x64.png';

            case 'gpx':
                return 'gpx-icon-64x64.png';
            case 'kml':
                return 'kml-icon-64x64.png';

            case 'asp':
                return 'asp-icon-64x64.png';
            case 'aspx':
                return 'aspx-icon-64x64.png';
            case 'cer':
                return 'cer-icon-64x64.png';
            case 'cfm':
                return 'cfm-icon-64x64.png';
            case 'css':
                return 'css-icon-64x64.png';
            case 'htm':
            case 'html':
            case 'xhtml':
                return 'htm-icon-64x64.png';
            case 'js':
                return 'js-icon-64x64.png';
            case 'jsp':
                return 'jsp-icon-64x64.png';
            case 'php':
                return 'php-icon-64x64.png';
            case 'rss':
                return 'rss-icon-64x64.png';

            case 'fnt':
            case 'fon':
            case 'otf':
            case 'ttf':
                return 'fon-icon-64x64.png';

            case 'cab':
                return 'cab-icon-64x64.png';
            case 'cur':
                return 'cur-icon-64x64.png';
            case 'dll':
                return 'dll-icon-64x64.png';
            case 'icns':
                return 'icns-icon-64x64.png';
            case 'ico':
                return 'ico-icon-64x64.png';
            case 'sys':
                return 'sys-icon-64x64.png';

            case 'cfg':
                return 'cfg-icon-64x64.png';
            case 'ini':
                return 'ini-icon-64x64.png';

            case '7z':
            case 'rar':
                return '7z-icon-64x64.png';
            case 'bz2':
                return 'bz2-icon-64x64.png';
            case 'cbr':
                return 'cbr-icon-64x64.png';
            case 'deb':
                return 'deb-icon-64x64.png';
            case 'gz':
                return 'gz-icon-64x64.png';
            case 'pkg':
                return 'pkg-icon-64x64.png';
            case 'rpm':
                return 'rpm-icon-64x64.png';
            case 'sitx':
                return 'sitx-icon-64x64.png';
            case 'zip':
                return 'zip-icon-64x64.png';

            case 'dmg':
                return 'dmg-icon-64x64.png';
            case 'iso':
                return 'iso-icon-64x64.png';

            case 'c':
                return 'c-icon-64x64.png';
            case 'class':
                return 'class-icon-64x64.png';
            case 'cpp':
                return 'cpp-icon-64x64.png';
            case 'cs':
                return 'cs-icon-64x64.png';
            case 'dtd':
                return 'dtd-icon-64x64.png';
            case 'fla':
                return 'fla-icon-64x64.png';
            case 'h':
                return 'h-icon-64x64.png';
            case 'java':
                return 'java-icon-64x64.png';
            case 'lua':
                return 'lua-icon-64x64.png';
            case 'm':
                return 'm-icon-64x64.png';
            case 'pl':
                return 'pl-icon-64x64.png';
            case 'py':
                return 'py-icon-64x64.png';
            case 'sh':
                return 'sh-icon-64x64.png';
            case 'vcxprox':
                return 'vcxproj-icon-64x64.png';
            case 'xcodeproj':
                return 'xcodeproj-icon-64x64.png';

            case 'bak':
                return 'bak-icon-64x64.png';

            case 'ics':
                return 'ics-icon-64x64.png';
            case 'torrent':
                return 'torrent-icon-64x64.png';


            default:
                return 'generic-icon-64x64.png';
        }
    }
}
