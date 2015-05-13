<?php
header('Content-Type: text/html; charset=utf-8');

$headCode = <<<EOF
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<style type="text/css"> 
textarea { 
width: 100%;
overflow: auto; 
word-break: break-all
} 
</style>
<script>
function submit(e){
    e.submit();
}
</script>\r\n
EOF;

$formCode = <<<EOF
<form method="post" action="" id="vhdata">
    <textarea placeholder="Paste vHomework amf.do submit data here" name="data" id="data" rows="10" autofocus>{vH_data}</textarea>
    <input type="test" name="sentnum" placeholder="Sentence numbers" value="{st_data}" onchange="submit(this)">
    <input type="submit">
</form>
EOF;


class vHomeWork_Parser{
    public $vH_data;
    
    public function __construct(){
        $this->vH_data = (isset($_POST['data'])) ? trim($_POST['data']) : '';
    }
    
    private function _showHTMLhead(){
        global $headCode;
        return print($headCode);
    }

    private function _showDataForm($data){
        global $formCode;
        return print(str_replace('{st_data}', @$_POST['sentnum'], str_replace('{vH_data}', $data, $formCode)));
    }
    
    private function _Exception($errorMessage = 'Unknown error'){
        global $footer;
        return die("Error information: {$errorMessage}{$footer}");
    }
    
    public function render(){
        /* 输出 HTML 基本代码 */
        self::_showHTMLhead();
        
        /* 判断是否已经送出 vH_data */
        if($this->vH_data !== ''){
            // 已送出
            self::_showDataForm($this->vH_data);
            echo self::parseData();
        } else {
            // 未送出
            self::_showDataForm('');
        }
        
        /* 頁尾 */
        global $footer;
        echo $footer;
        
        return true;
    }
    
    public function parseData($data = ''){
        /* 确定资料可用性 */
        if($data == '' && $this->vH_data != '' && isset($_POST['sentnum'])){
            $data = $this->vH_data;
            $sentnum = $_POST['sentnum'];
        } elseif($data == ''){
            self::_Exception('No data provided');
        }
        
        /* 處理資料 */
        if(strpos($data, '?') !== false){
            $data = explode('?', $data);
            $data = $data[1];
        }
        parse_str($data, $output);  // 參數 => array

        /* 存好去 value2 的完整參數，等會要直接替換的 */
        $originValue2 = urlencode($output['value2']);
        $data = str_replace($originValue2, '{[]}', $data);
        
        /* 處理資料，解析 JSON */
        $json = $output['value2'];
        $json = json_decode($json, true);
        
        /* 生成每句 92~97 分的 LINK */
        $echo = '';
        for($i = 1; $i <= $sentnum; $i++) {
            $temp = $json;
            $randScore = rand(92, 97);
            $temp['itemsdata'][0]['score'] = $randScore;
            $temp['itemsdata'][0]['sentenceid'] = $i;
            $temp = json_encode($temp);
            $temp = urlencode($temp);
            $temp = str_replace('{[]}', $temp, $data);
            $temp = 'http://www.vhomework.com/messagebroker/amf.do?' . $temp;
            
            /* 轉為 HTML CODE */
            $a = "<a href={$temp}>{$temp}</a>";
            $echo .= $a . '<br /><br />';
            
            /* 清理變數 */
            unset($temp);
            unset($a);
        }
        
        return $echo;
        
        
        
        
        
        /*
        $baseurl = str_replace(urlencode($output['value2']), '{replace}', $data);
        $sentnum = $_POST['sentnum'];
        $data = json_decode($data, true);
        var_dump($data);
        $data['itemsdata'][0]['score'] = '100';
        $data['itemsdata'][0]['sentenceid'] = '0';
        
        $echo = '';
        for($i = 1; $i <= $sentnum; $i++) { 
            $tdata = $data;
            $tdata['itemsdata'][0]['sentenceid'] = "{$i}";
            $a = str_replace('{replace}', urlencode(json_encode($tdata)), $baseurl);
            $echo .= "<a href={$a}>$a</a><br /><br />";
            unset($tdata);
        } 
        */
    }
}

$vH_Parser = new vHomeWork_Parser();
$vH_Parser->render();