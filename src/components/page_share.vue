<style scoped>
.topPage {
    text-align: center;
    background: rgba(245, 247, 249, 0.38);
    padding: 50px 0;
}
h1{
    font-size: 30px;
}
.serachBox {
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

.oneCard {
    width: 200px;
    cursor:default;
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
.eavatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    border: 1px solid #ccc;
    padding: 2px;
}
.d-a{
    height: 64px;
    display: inline-block;
}
.d-a img{
    display: block;
}
.d-b{
    height: 64px;
    display: inline-block;
    position: relative;
    top: -10px;
}
.d-b a{
    display: block;
}
</style>
<template>
    <div class="page-home">
        <div class="topPage">
            <Row>
                <Col span="7" :xs="24" :sm="7">
                    <h1>图标包#{{id}}</h1>
                    <h2 v-if="share.auser.id==''">作者(酷安ID)：<a :href="'https://www.coolapk.com/u/'+share.author" target="_blank">{{share.author}}</a></h2>
                    <h2 v-else>
                        <div class="d-a">
                            <img :src="share.auser.avatar" class="eavatar">
                        </div>
                        <div class="d-b">
                            <div>作者：</div>
                              <router-link :to="'/author/'+share.auser.id">{{share.auser.nickname}}</router-link>
                        </div>
                    </h2>
                </Col>
                <Col span="17" :xs="24" :sm="17">{{share.desc}}</Col>
            </Row>
        </div>
        <div class="List">
             <Card class="oneCard" v-for="(item, index) in icons">
                <p slot="title">
                    <img :src="item.img" :alt="item.package">
                </p>
                <p>{{item.package}}</p>
                <div v-if="cuser==share.auser.id" > 
                    <i-Button size="small" @click.stop="wIcon(item)">加入清单</i-Button>
                    <i-Button type="error" size="small" @click.stop="dIcon(item)">删除</i-Button>
                </div>
                <i-Button v-else long size="small" @click.stop="wIcon(item)">加入清单</i-Button>
            </Card>
        </div>
    </div>
</template>
<script>
    import ajax from 'djax'
    export default {
        data () {
            try{
                var cuser=window.gxicon_user.id;
            }catch(e){
                var cuser="none";
            }
            return {
                id:"",
                share:{
                    author:"",
                    auser:false,
                    desc:""
                },
                icons:[],
                cuser:cuser
            }
        },
        methods:{
            dlIcon:function(url){
                window.open(url);
            },
            wIcon:function(e){
                this.$root.$emit("iconSave",e);
            },
            dIcon:function(e){
                var that=this;
                this.$Modal.confirm({
                    title: '真的要删除吗',
                    content: '要恢复只能py了哦',
                    okText:"删删删",
                    onOk: () => {
                        ajax({
                            url:that.$root.server+"icon/delete/"+e.id,
                            xhrFields: {withCredentials: true},
                            dataType:"json"
                        }).done(function(){
                            that.$Message.success('删掉了');
                            update(that);
                        });
                    }
                });
            }
        },
        created:function(){
            var that=this;
            update(that);
            this.$root.$on("onLogin",function(user){
                that.cuser=user.id
            });
        },
        beforeRouteUpdate:function(to, from, next){
            next();
            update(this);
        }
    }
    function update(that){
        that.id=that.$route.params.id;
            ajax({
                url:that.$root.server+"icon/byshare/"+that.id,
                type:"get",
                dataType:"json"
            }).done(function(e){
                if(e.share[0].author.indexOf("e:")==0){
                    ajax({
                        url:that.$root.passport+"api/user/public/"+e.share[0].author.split(":")[1],
                        type:"get",
                        dataType:"json"
                    }).done(function(f){
                        e.share[0].auser=f.user;
                        e.share[0].author=f.user.nickname;
                        that.share=e.share[0];
                        that.icons=e.data;
                    });
                }else{
                    e.share[0].auser={
                        id:"",avatar:"",nickname:""
                    };
                    that.share=e.share[0];
                    that.icons=e.data;
                }

            })
    }
</script>