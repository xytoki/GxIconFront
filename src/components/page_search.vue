<style scoped>
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
    left:20px; 
}
.searchBox input {
    border-color: #2d8cf0;
}
.searchBtn{
    position: relative; 
    left:-20px;
}
.List{
    position: relative;
}
.paged {
    text-align: center;
}
.oneCard {
    width: 200px;
    cursor:pointer;
    text-align: center;
    display: inline-block;
    margin: 5px;
}

.oneCard .ivu-card-head p {
    height: 100px;
    text-align: center;
}
.oneCard .ivu-card-head>p{
    background-position: 0px 0px, 10px 10px;
    background-size: 20px 20px;
    background-image: linear-gradient(45deg, #eee 25%, transparent 25%, transparent 75%, #eee 75%, #eee 100%),linear-gradient(45deg, #eee 25%, white 25%, white 75%, #eee 75%, #eee 100%);
}
.oneCard .ivu-card-head p img {
    width: auto;
    height: 100px;
}
@media screen and (max-width: 768px) { 
    .oneCard{
        width: 45% !important;
    }
    .oneCard .ivu-card-head p img {
        width: 100%;
        height: auto;
    }
    .oneCard .ivu-card-head p {
        height:auto;
    }
}
.List{
    text-align: center;
}
</style>
<style>

.ivu-select-not-found li:not([class^=ivu-]) {
        display: none;
}</style>
<template>
    <div class="page-home">
        <div class="topPage">
            <Select class="searchBox" 
                placeholder="输入包名 支持搜索"
                v-model="package"
                filterable
                remote
                :remote-method="searchIcon"
                :loading="false">
                <Option v-for="(option, index) in options" :value="option.pkg" :key="index" :label="option.pkg">
                    <div>{{option.pkg}}</div>
                    <div style="color:#ccc">{{option.label}}</div>
                </Option>
            </Select>
             <!--Input v-model="package" size="large" placeholder="输入应用包名" class="serachBox" @keyup.native="searchKD"></Input-->
             <Button type="primary" class="searchBtn" shape="circle" icon="android-search" @click="searchKD({which:13})"></Button>
        </div>
        <div class="List">
             <Card @click.native="viIcon(item.share_id)" class="oneCard" v-for="(item, index) in list">
                <p slot="title">
                    <img :src="item.img" :alt="item.package">
                </p>
                <p>{{item.package}}</p>
                <p v-if="!item.auser">作者(ID)：<a @click.stop :href="'https://www.coolapk.com/u/'+item.author" target="_blank">{{item.author}}</a></p>
                <p v-else>作者：<router-link @click.native.stop :to="'/author/'+item.auser.id">{{item.auser.nickname}}</router-link></p>
                <i-Button long size="small" @click.stop="wIcon(item)">加入清单</i-Button>
            </Card>
            <Spin fix v-if="spinShow" size="large"></Spin>
            <Page class="paged" :total="total" :page-size="50" @on-change="pageD" show-elevator></Page>
        </div>
    </div>
</template>
<script>
    import ajax from 'djax';
    export default {
        data () {
            return {
                spinShow:true,
                options:[],
                packagea:{},
                package:this.$route.params.package,
                page:this.$route.query.p,
                total:0,
                list:[]
            }
        },
        methods:{
            
             searchIcon (search) {
                var that=this;
                if (search !== '') {
                    ajax(that.$root.server+"code/search/"+search)
                    .done(resp => {
                        that.options = resp.data
                        if(resp.data.length==0)that.options = [{
                            "label":search,
                            "pkg":search,
                            "sum2":"",
                            "launcher":"",
                            "sum":"",
                            "slot":search
                        }];
                    })
                } else {
                    that.options = [];
                }
            },
            searchKD:function(e){
                if(e.which==13){
                    this.$router.push("/search/"+this.package);
                    doSearch(this);
                }
            },
            pageD:function(p){
                this.page=p;
                this.$router.push("/search/"+(this.package||"")+"?page="+p)
                doSearch(this);
            },
            dlIcon:function(url){
                window.open(url);
            },
            wIcon:function(e){
                this.$root.$emit("iconSave",e);
            },
            viIcon:function(url){
                this.$router.push("/share/"+url);
            }
        },
        created:function(){
            doSearch(this);
        }
    }
    function doSearch(that){
        that.spinShow=true;
        var data={};
        if(that.package!=""&&that.package!=undefined)data.package=that.package;
        if(that.page!=""&&that.page!=undefined)data.page=that.page;
        ajax({
            url:that.$root.server+"icon/search",
            type:"get",
            data:data,
            dataType:"json"
        }).done(function(e){
            function unique(arr){
                var res=[];
                for(var i=0,len=arr.length;i<len;i++){
                    var obj = arr[i];
                    for(var j=0,jlen = res.length;j<jlen;j++){
                        if(res[j]===obj) break;            
                    }
                    if(jlen===j)res.push(obj);
                }
                return res;
            }
            console.log(e)
            var eu=[];
            for(var i in e.data){
                if(e.data[i].author.indexOf("e:")==0)eu.push(e.data[i].author.split(":")[1]);
            }
            unique(eu);
            ajax({
                url:that.$root.passport+"api/user/mpublic/"+eu.join(",")
            }).done(function(f){
                for(var i in e.data){
                    for(var ii in f.user){
                        if(e.data[i].author==("e:"+f.user[ii].id)){
                            e.data[i].auser=f.user[ii];
                            e.data[i].author=f.user[ii].nickname;
                            break;
                        }
                    }
                }
                that.list=e.data;
                that.total=Number(e.total);
                that.spinShow=false;
            }).fail(function(){
                that.list=e.data;
                that.total=Number(e.total);
                that.spinShow=false;
            })
        })
    }
</script>