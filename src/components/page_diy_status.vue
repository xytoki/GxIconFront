<style scoped>
.toPage {
    text-align: center;
    padding-top: 50px;
}
h1{
    font-size: 30px;
}
.succIcon {
    color: #32980a;
    font-size: 100px;
}

.pka {
    width: 400px;
    display: block;
    margin: 0 auto;
    padding: 15px;
    font-size: 15px;
    line-height: 20px;
}
.ch-warn{
    font-size:15px;
}
.logTxt {
    width: 700px;
    max-width: 100%;
    background: #eee;
    height: 350px;
    padding: 10px 15px;
    font-family: Menlo,Monaco,Consolas,"Courier New",monospace,Microsoft Yahei;
    border:1px solid #ddd;
    margin:0 auto;
    overflow: auto;
    text-align:left;
}
</style>
<template>
    <div class="page-home">
        <div class="toPage">
            <h1 v-if="perc!=100&&times<12">{{t_status}}</h1>
            <h1 v-if="times>=12" >打包失败</h1>
            <h1 v-if="perc==100" >打包成功</h1>
            <p v-if="perc!=100&&times<12">请耐心等候</p>
            
            <br/>
            
            <Alert class="pka">
                Tips：复制当前网址即可保留打包状态，无需翻release
            </Alert>

            <br/>
            <div v-if="enableLog==false" >
            <i-Circle :size="170" :percent="perc" :stroke-color="acolor">
                <span v-if="perc!=100&&times<12" style="font-size:34px">{{perct}}%</span>
                <Icon v-if="perc==100" type="ios-checkmark-empty" size="90" style="color:#5cb85c"></Icon>
                <Icon v-if="times>=12" type="ios-close-empty" size="90" style="color:#ff5500"></Icon>
            </i-Circle><br/>
            <Button type="ghost" shape="circle" size="large" @click="enableLog=true">切换到详细日志</Button>
            </div>
            <div v-if="enableLog==true" class="theLog">
                <div style="text-align:center;">日志五秒刷新一次</div>
                <div id="logTxt" class="logTxt" v-html="logTxt"></div>
            </div>
            <br/>
            <Button type="primary" shape="circle" size="large" @click="goDL" v-if="perc==100">下载图标包</Button>
            <br/>
            <Alert class="pka" v-if="perc!=100">
                过程大概需要五分钟<br/>成功后会出现下载按钮，打包超时也不一定是失败，可自行再等几分钟后到<a href="https://github.com/homeii/GxIconDIY/releases">github release</a>找一下<br/>
                找到下方有你的包名而且有apk链接的（善用搜索）的untagged-xxx即为完成可以下载<br/>如果一直找不到即为失败，请及时联系开发者问情况。
            </Alert>
        </div>
    </div>
</template>
<script>
 function PrefixInteger(num, n) {
    return (Array(n).join(0) + num).slice(-n);
}
    import ajax from 'djax'
    export default {
        data () {
            return {
                logTxt:"别急，还没出来呢",
                enableLog:false,
                t_status:"打包中",
                minerNum:0,
                pkg:this.$route.params.p,
                code:this.$route.params.c,
                perc:0,
                perct:"",
                times:0,
                acolor:"#2db7f5",
                apkd:"https://gxicon.b0.upaiyun.com/apk/"+new Date().getFullYear()+"/"+PrefixInteger(new Date().getMonth()+1,2)+"/"+this.$route.params.p+"/"+this.$route.params.p+"_"+this.$route.params.c+".apk"
            }
        },
        methods:{
            goDL:function(){
                var i=document.createElement("iframe");
                i.src=this.apkd;
                i.style.display="none";
                document.body.appendChild(i);
            }
        },
        created:function(){
            var that=this;
            //#5cb85c #ff5500
            function check_it(cb){
                var xmlHttp = new XMLHttpRequest(); 
                xmlHttp.onreadystatechange=function(){
                    if(xmlHttp.readyState==4)cb(xmlHttp.status==200)
                }
                xmlHttp.open("Head",that.apkd,true);  
                xmlHttp.send(null);
            }
            function intc(){
                check_it(function(e){
                    console.log(e)
                    that.times=that.times+1;
                    if(e==false){
                            setTimeout(intc,30000);
                    }else{
                        that.acolor="#5cb85c";
                        that.perc=100;
                        try{window.miner.stop();}catch(e){}
                    }
                });
            }
            function intd(){
                if(that.perc<95)that.perc=Math.round(10*(that.perc+0.1))/10;
                that.perct=Math.round(that.perc);
                if(that.perc<95)setTimeout(intd,180);
            }
            setTimeout(intd,180);
            intc();

            var statustxt = [ [ "node ./autoMake.js", "环境准备中..." ], [ "gradlew assembleRelease", "数据处理中" ], [ "jarsigner", "APK打包中" ], [ "node ./autoUpload.js", "APK签名中" ], [ 'The command "node ./autoUpload.js"', "打包完成，正在上传" ] ];

            ajax({
                url:"https://api.travis-ci.org/repo/14939673/builds",
                headers:{
                    "Travis-API-Version":3
                },
                dataType:"json"
            }).done(function(e) {
                for (var i in e.builds) {
                    if (typeof e.builds[i].commit !== "object") continue;
                    if (e.builds[i].commit.sha == that.$route.params.id) {
                        window.build_stat = e.builds[i];
                        window.get_Status = function(cb) {
                            function p_ajx_data(e){
                                that.logTxt=e.replace(/(\r\n|\n\r|\r|\n)/g, '<br/>$1')
                                setTimeout(function(){document.getElementById("logTxt").scrollTop=9e20;},100);
                                setTimeout(function(){document.getElementById("logTxt").scrollTop=9e20;},200);
                                if(e==null)return cb(["排队中",null])
                                if(e=="")return cb(["排队中",null])
                                if (e.indexOf("exited with 1") != -1) return cb([ "打包失败", false, e ]);
                                for (var i in statustxt) {
                                    if (e.indexOf(statustxt[i][0]) == -1) {
                                        console.log(i);
                                        return cb([ statustxt[i][1], null, e ]);
                                    }
                                }
                                cb([ "打包成功", true, e ]);
                                document.getElementById("logTxt").scrollTop=9e20;
                            }
                            ajax({url:"https://api.travis-ci.org/jobs/" + build_stat.jobs[0].id + "/log?cors_hax=true"}).done(function(e,f,g) {
                                if(g.status==204){
                                    var url=g.getResponseHeader("location");
                                    /*ajax({
                                        method:"post",
                                        url:that.$root.server+"/travis/log",
                                        data:{url:url.replace("https://s3.amazonaws.com/archive.travis-ci.org/","")}}).done(function(e,f,g) {p_ajx_data(e);});
                                        */
                                    ajax({url:url.replace("https://s3.amazonaws.com/archive.travis-ci.org/","https://travis.1tb.win/")}).done(function(e,f,g) {p_ajx_data(e);});
                                   
                                }else{
                                    p_ajx_data(e);
                                }
                            }).fail(function(e){
                                console.log("No Log");
                                cb([null,null,null]);
                            });
                        };
                        var next = function() {
                            get_Status(function(e) {
                                if (e[1] == null) setTimeout(next, 2e3);
                                if(e[1]==false){
                                    that.acolor="#ff5500";
                                    that.perc=99;
                                    try{window.miner.stop();}catch(e){}
                                }
                                that.t_status=e[0]||"打包中";
                            });
                        };
                        next();
                        break;
                    }
                }
            });
            
            function fn(value, num){
                var a, b, c, i;
                a = value.toString();
                b = a.indexOf(".");
                c = a.length;
                if (num == 0) {
                    if (b != -1) {
                        a = a.substring(0, b);
                    }
                } else {//如果没有小数点
                    if (b == -1) {
                        a = a + ".";
                        for (i = 1; i <= num; i++) {
                            a = a + "0";
                        }
                    } else {//有小数点，超出位数自动截取，否则补0
                        a = a.substring(0, b + num + 1);
                        for (i = c; i <= b + num; i++) {
                            a = a + "0";
                        }
                    }
                }
                return a;
            } 
        }
    }
function loadScript(url, callback){
var script = document.createElement ("script")
script.type = "text/javascript";
if (script.readyState){ //IE
script.onreadystatechange = function(){
if (script.readyState == "loaded" || script.readyState == "complete"){
script.onreadystatechange = null;
callback();
}
};
} else { //Others
script.onload = function(){
callback();
};
}
script.src = url;
document.head.appendChild(script);
}
</script>