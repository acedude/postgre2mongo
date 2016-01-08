var vm = new Vue({
    el: '#app',
    data: {},
    ready: function () {
        this.loadData();
    },
    methods: {
        number_format: function (num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
        },
        toggleProcessing: function () {
            this.$http.get('api/control', function (reply, status, request) {
                if (status == 200) {
                    this.$set('info.isProcessing', reply);
                }
            });
        },
        loadData: function () {
            this.$http.get('api/status', function (reply, status, request) {
                if (status == 200) {
                    this.$set('info', reply.info);
                    this.$set('connections', reply.connections);
                    this.$set('tables', reply.tables);
                    this.$set('rows', reply.rows);
                }
            });
        }
    }
});

setInterval(function () {
    vm.loadData();
}, 1000);