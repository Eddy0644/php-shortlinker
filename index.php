<?php
$GLOBALS["log_text"]="";
$db=unserialize(file_get_contents("db.pdata"));
if(isset($_GET["s"])){
    $tk=$_GET["s"];//URL Token

    if(isset($db["link"][$tk])){
        //Ready for jumping & write data to log
        $db["stat"][$tk]["lastAccess"]=date("Ymd-His");
        $db["stat"][$tk]["triggerCount"]++;
        header("HTTP/1.1 302 Moved Temporarily");
        header("Location:".$db["link"][$tk]);
        logger("DEBUG","incoming new request, token is {".$tk."}");
        gracefullyExit($db);
    }else{
        //Bad Request
        header("HTTP/1.1 400 Bad Request");
        echo "<span style=\"font-size:larger\">Sorry, but it seems that your token in URL ($tk) doesn't refer to a real item in our database.Maybe you could check the spell?</span>";
        logger("INFO","incoming new request but not found, token is {".$tk."}");
        gracefullyExit($db);
    }
}
if(isset($_REQUEST["adm"])){
    //No authenticating methods,TODO.
    switch ($_REQUEST["act"]){
        case "add":{
            $tk=$_REQUEST["token"];
            $link=base64_decode($_REQUEST["link"]);
            $db["stat"][$tk]=["lastAccess"=>"","triggerCount"=>0];
            $db["link"][$tk]=$link;
            echo "<h2>Successfully added an entry:token is ($tk), link is ($link)</h2>";
            logger("INFO","added new token, tk is {".$tk."},link is {".$link."}");
            gracefullyExit($db);
        }break;
        case "del":{
            $tk=$_REQUEST["token"];
            $link=$db["link"][$tk];
            unset($db["stat"][$tk]);
            unset($db["link"][$tk]);
            logger("INFO","admin deleted a token, tk is {".$tk."},original-link is {".$link."}");
            gracefullyExit($db);
        }break;
//        case "":{
//        }break;
        default:{
            //Present a brief introduction of all the links.
//            TODO0:Provide a control panel of them.
            ?>

<table border="1" style="border-collapse: collapse">
    <tbody>
        <tr><td colspan="3">Overview of all the shortened URLs</td></tr>
        <tr>
            <td>Short Token</td>
            <td>Link</td>
            <td>triggerCount</td>
            <td>lastAccess</td>
            <td>Operations</td>
        </tr>
            <?php foreach($db["link"] as $k=>$v){     ?>
        <tr>
<!--            <td>--><?//=$k?><!--</td>-->
            <td><a href="https://c.gacenwinl.cn/link/?s=<?=$k?>"><?=$k?></a></td>
            <td><a href="<?=$v?>"><?=$v?></a></td>
            <td><?=$db["stat"][$k]["triggerCount"]?></td>
            <td><?=$db["stat"][$k]["lastAccess"]?></td>
            <td><a href="#" onclick="doDelToken(this)">✖</a></td>
        </tr>
            <?php } ?>
    </tbody>
</table><hr/>
<form action="" method="post" style="border:solid 1px gray">
<!--    <input type="hidden" name="act" value="add">-->
    Action:<select name="act" id="act_Select">
        <option value="add" selected>➕</option>
        <option value="del" id="act_opt_Del">✖</option>
    </select><br/>
    Token: <input type="text" name="token" id="token"><br/>
    Link Target: <input type="text" name="link"><br/>
    <input type="submit" onclick="doNewToken(this)" value="{ Do it !! }">
</form>
<script>
    function doNewToken(ele){
        let linkEle=ele.previousElementSibling;
        linkEle.value=btoa(linkEle.value);
    }
    function doDelToken(ele){
        let ele2=ele.parentElement.previousElementSibling.previousElementSibling.previousElementSibling.previousElementSibling;
        window.token.value=ele2.innerText;
        window.act_opt_Del.selected=true;
    }
</script>
<style>
    td{
        text-align: center;
        padding:5px;
    }
</style>

<?php
            //End of control panel.
            logger("INFO","admin refreshed the graph.");
            gracefullyExit($db);
        }break;
    }
}



function logger($type,$text){
    $GLOBALS["log_text"].=sprintf("%s [%-5s] %s\n",date("Ymd His"),$type,$text);
}
function gracefullyExit($db){
    file_put_contents("db.pdata",serialize($db));
    file_put_contents("log.txt",$GLOBALS["log_text"].file_get_contents("log.txt"));
    exit;
}