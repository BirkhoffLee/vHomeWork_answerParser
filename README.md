# vHomework 智能英語學習平台的答案解析器
http://www.vhomework.com
用法：在本地搭建一個 WAMP 環境，將 parser.php 放入網站主資料夾內。然後打開你的 vHomework，進入選擇題目的頁面，接著打開 Chrome 開發人員工具的 Network，然後按下題目的大框框，他會說在載入中，載入完之後找到 repository.action?packageId 開頭的 request。點進去那個 request，選擇選項卡 Response，將下面所有 XML Code 複製到 http://localhost/parse.php 中的大框框，點一下空白處即可。
