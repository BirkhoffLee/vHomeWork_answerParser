<?php
header('Content-Type: text/html; charset=utf-8');

$headCode = <<<EOF
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
    <textarea placeholder="Paste vHomework data here" name="data" id="data" onchange="submit(this)" rows="10" autofocus>{vH_data}</textarea>
</form>
EOF;


class vHomeWork_Parser{
    public $vH_data;
    
    public function __construct(){
        $this->vH_data = (isset($_POST['data'])) ? trim($_POST['data']) : '';
        
        /* 输出 HTML 基本代码 */
        self::_showHTMLhead();
        
        /* 判断是否已经送出 vH_data */
        if($this->vH_data !== ''){
            // 已送出
            self::_showDataForm($this->vH_data);
            self::parseData();
        } else {
            // 未送出
            self::_showDataForm('');
        }
    }
    
    private function _showHTMLhead(){
        global $headCode;
        return print($headCode);
    }

    private function _showDataForm($data){
        global $formCode;
        return print(str_replace('{vH_data}', $data, $formCode));
    }
    
    private function _Exception($errorMessage = 'Unknown error'){
        return die('Error information: ' . $errorMessage);
    }
    
    public function parseData($data = ''){
        /* 确定资料可用性 */
        if($data == '' && $this->vH_data != ''){
            $data = $this->vH_data;
        } elseif($data == ''){
            self::_Exception('No data provided');
        }
        
        
        /* 解析资料 */
        $xmlObj = xml_parser_create();
        xml_parser_set_option($xmlObj, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($xmlObj, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($xmlObj, $data, $xml_Array);
        xml_parser_free($xmlObj);
        
        /* 确定 XML 格式 */
        if(empty($xml_Array)){
            self::_Exception('XML data invalid!');
        }
        
        
        /* 判断题目类型, 解析答案 */
        $answer = "";
        switch ($xml_Array[7]['attributes']['id']){
            case 3:
            case 7:
                $questionType = ($xml_Array[7]['attributes']['id'] == 7) ? "单选题" : "听力选择题";
                $questionName = "";  // 资料乱码，无法判别
            
                /* 初始化所需变量 */
                $data2 = array();
                $data3 = array();
            
                /* 分割资料 */
                $data = explode("<Answer>", $data);
                unset($data[0]);
            
                /* 获得答案 */
                foreach ($data as $key => $value) {
                    $tempdata = explode("</Answer>", $value);
                    array_push($data2, $tempdata[0]);
                }
            
                /* 将答案编号转换成字母形式 */
                foreach ($data2 as $key => $value) {
                    $nvalue = str_replace('4', 'D', str_replace('3', 'C', str_replace('2', 'B', str_replace('1', 'A', $value))));
                    array_push($data3, $nvalue);
                }
            
                /* 转换 $data3 阵列至 HTML code */
                foreach ($data3 as $key => $value) {
                    $num = $key + 1;
                    $answer .= "<br />第 {$num} 題： {$value}";
                }

                break;
            
            case 8:
                $questionType = "填空题";
                $questionName = $xml_Array[14]['value'];
            
                /* 初始化所需变量 */
                $data2 = array();
                $data3 = array();
                $data4 = array();
            
                /* 分割资料 */
                $data = explode("<Answers>", $data);
                unset($data[0]);
            
                /* 获得答案 */
                foreach ($data as $key => $value) {
                    $tempdata = explode("</Answers>", $value);
                    array_push($data2, trim($tempdata[0]));
                }
                foreach ($data2 as $key => $value) {
                    $tempdata = str_replace('</Answer>', '', $value);
                    array_push($data3, trim($tempdata));
                }
            
                /* 转换答案格式 */
                foreach ($data3 as $key => $value) {
                    $tempdata = explode('>', $value);
                    unset($tempdata[0]);
                    foreach ($tempdata as $key => $value) {
                        $tempdata2 = explode('<Answer', $value);
                        array_push($data4, str_replace('{[]}', ' 或者 ', trim($tempdata2[0])));
                    }
                }
            
                /* 转换 $data4 阵列至 HTML code */
                foreach ($data4 as $key => $value) {
                    $num = $key + 1;
                    $answer .= "<br />第 {$num} 空f： {$value}";
                }
                break;
        }

        /* 输出答案 */
        echo "[{$questionType}] : {$questionName}\r\n{$answer}";
    }
}

$vH_Parser = new vHomeWork_Parser();