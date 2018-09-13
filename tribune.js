document.addEventListener('DOMContentLoaded', function () {
    var tribune = {
        posts: [],
        palmipede: document.getElementById("palmipede"),
        init: function () {
            var self = this;
            fetch('/user/plugins/tribune/backend2html.pegjs').then(function (response) {
                return response.text();
            }).then(function (responseText) {
                self.taab_backend2html = peg.generate(responseText);
                self.refresh();
                setInterval(function () {
                    self.refresh();
                }, 30000);
            });
            self.palmipede.addEventListener("submit", function (e) {
                e.preventDefault();
                fetch('?backend=tsv', {
                    method: "POST",
                    body: new FormData(self.palmipede)
                }).then(function (response) {
                    return response.text();
                }).then(function (responseText) {
                    self.parseBackend(responseText);
                    self.palmipede.elements.message.value = '';
                });
            });
        },
        refresh: function () {
            var self = this;
            fetch('?backend=tsv').then(function (response) {
                return response.text();
            }).then(function (responseText) {
                self.parseBackend(responseText);
            });
        },
        parseBackend: function (responseText) {
            var self = this;
            var newPosts = responseText.split(/\r\n|\n/).map(function (line) {
                var post = line.split(/\t/);
                if (post.length >= 5) {
                    var time = post[1];
                    var formattedTime = time.substr(0, 4) + "-" + time.substr(4, 2) + "-" + time.substr(6, 2) + "T" + time.substr(8, 2) + ":" + time.substr(10, 2) + ":" + time.substr(12, 2);
                    var htmlMessage = self.taab_backend2html.parse(post[4]);
                    return {id: post[0], time: formattedTime, info: post[2], login: post[3], message: htmlMessage};
                } else {
                    return false;
                }
            }).filter(function (post) {
                return post && post.id && post.time && post.message;
            }).concat(self.posts);
            self.posts = newPosts.sort(function (a, b) {
                return b.id - a.id;
            }).filter(function (elem, pos) {
                return newPosts.findIndex(function (elem2) {
                    return elem.id === elem2.id;
                }) === pos;
            });
            document.getElementById('tribune').innerHTML = self.posts.reduce(function (html, post) {
                return html + '<article><time title="' + post.time + '">' + post.time.substr(11) + '</time> <cite title="' + post.info + '">' + (post.login || 'coward') + '</cite> <p>' + post.message + '</p></article>';
            }, '');
        }
    };
    tribune.init();
});