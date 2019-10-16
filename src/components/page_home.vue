<style scoped>
.topPage {
    text-align: center;
    background: rgba(245, 247, 249, 0.38);
    padding-top: 50px;
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
</style>
<template>
    <div class="page-home">
        <div class="topPage">
            <h1>分享，你的图标</h1>
             <Input v-model="package" size="large" placeholder="输入应用包名" class="serachBox" @keydown.native="searchKD"></Input>
             <Button type="primary" class="searchBtn" shape="circle" icon="android-search"></Button>
        </div>
        <div class="news">
            <h2>最新动态</h2>
            开发中
        </div>
    </div>
</template>
<script>
    export default {
        data () {
            return {
                package:""
            }
        },
        methods:{
            searchKD:function(e){
                if(e.which==13){
                    this.$router.push("/search/"+this.package);
                }
            },
        },
        created:function(){
        }
    }
</script>