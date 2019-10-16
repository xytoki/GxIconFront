<style scoped>
h1{
    font-size: 30px;
}
.topPage {
    text-align: center;
    background: rgba(245, 247, 249, 0.38);
    padding: 20px;
}
h1{
    font-size: 30px;
}
.ivu-select-dropdown-list *{
    text-align: left !important;
}
.serachBox { 
    text-align: left !important;
    width: 300px;
    position: relative;
}
.searchBox input {
    border-color: #2d8cf0;
}
</style>
<template>
    <div class="page-home">
        <div class="topPage">
            <h1>App代码速查</h1>
            <p>点击包名可查看适配代码</p>
             <Input v-model="package" size="large" placeholder="输入应用名/包名" class="serachBox" icon="android-search" @on-click="searchKD" @keyup.native="searchKD"></Input>
        </div>
        <Table :columns="cols" :data="pkgs" @on-row-click="clickOne"></Table>
        <Modal
            v-model="showCode"
            title="Appfilter代码">
            <Input v-model="code" type="textarea" :rows="6"></Input>
            <div slot="footer"></div>
        </Modal>
    </div>
</template>
<script>
    import ajax from 'djax';
    export default {
        data () {
            return {
                pkgs:[],
                package:"",
                cols:[{title:"App",key:"label"},{title:"英文名",key:"labelEn"},{title:"包名",key:"pkg"},{title:"启动项",key:"launcher"}],
                code:"",
                showCode:false
            }
        },
        methods:{
            searchKD:function(e){
                throttle(this.searchKB,200,700,this,e)();
            },
            searchKB:function(e){
                var that=this;
                var search=this.package;
                if (search !== '') {
                    ajax(that.$root.server+"code/search/"+search)
                    .done(resp => {
                        that.pkgs = resp.data
                    })
                } else {
                    that.options = [];
                }
            },
            clickOne:function(d,i){
                this.showCode=true;
                d.drawable=codeAppName(d.labelEn||d.label);
		        if(d.drawable.trim()=="")d.drawable=codeAppName(d.pkg);
                this.code=(generateCode(d))
            }
        },
        created:function(){
        }
    }
function generateCode(app) {
	if(typeof(app.launcher)=="undefined")return "";
	var code = "<!-- " + app.label + " / ";
	if (!app.labelEn||app.labelEn.trim().length == 0) {
		code += app.label + " -->";
	} else {
		code += app.labelEn + " -->";
	}
	code += "\n<item component=\"ComponentInfo{" + app.pkg.trim() + "/" + app.launcher
		+ "}\" drawable=\"";
	code += app.drawable + "\" />";
	return code;
}
function codeAppName(name) {
  if (!name) {
    return "";
  }
  name = name.trim();
  if (name.length == 0) {
    return "";
  }
  // 注意不是 /^[A-Za-z][A-Za-z\d'\+-\. _]*$/
  if (/^[A-Za-z][A-Za-z\d'\+\-\. _]*$/.test(name)) {
    var res;
    while ((res = /([a-z][A-Z])|([A-Za-z]\d)|(\d[A-Za-z])/.exec(name)) != null) {
      name = name.replace(res[0], res[0].charAt(0) + "_" + res[0].charAt(1));
    }
    return name.toLowerCase()
      .replace(/'/g, "")
      .replace(/\+/g, "_plus")
      .replace(/-|\.| /g, "_")
      .replace(/_{2,}/g, '_');
  }
  return "";
}
var throttle = function (fn, delay, atleast,that,args) {
    var timer = fn.timer||null;
    var previous = null;

    return function () {
        var now = +new Date();

        if ( !previous ) previous = now;

        if ( now - previous > atleast ) {
            fn();
            // 重置上一次开始时间为本次结束时间
            previous = now;
        } else {
            clearTimeout(timer);
            fn.timer = setTimeout(function() {
                fn.apply(that,args);
            }, delay);
        }
    }
};
</script>