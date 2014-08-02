YUI.add('moodle-local_loginas-quickloginas', function(Y) {

    var ULP = {
        NAME : 'Login-as Manager',
        /** Properties **/
        BASE : 'base',
        SEARCH : 'search',
        PARAMS : 'params',
        URL : 'url',
        AJAXURL : 'ajaxurl',
        MULTIPLE : 'multiple',
        PAGE : 'page',
        COURSEID : 'courseid',
        USERS : 'users',
        USERCOUNT : 'userCount',
        REQUIREREFRESH : 'requiresRefresh',
        LASTSEARCH : 'lastPreSearchValue'
    };
    /** CSS classes for nodes in structure **/
    var CSS = {
        PANEL : 'user-loginas-panel',
        WRAP : 'ulp-wrap',
        HEADER : 'ulp-header',
        CONTENT : 'ulp-content',
        AJAXCONTENT : 'ulp-ajax-content',
        SEARCHRESULTS : 'ulp-search-results',
        TOTALUSERS : 'totalusers',
        USERS : 'users',
        USER : 'user',
        SEARCHBUTTONS : 'ulp-search-buttons',
        NEXTRESULTS : 'ulp-next-results',
        PREVRESULTS : 'ulp-prev-results',
        LIGHTBOX : 'ulp-loading-lightbox',
        LOADINGICON : 'loading-icon',
        FOOTER : 'ulp-footer',
        COUNT : 'count',
        PICTURE : 'picture',
        DETAILS : 'details',
        LOGINASLINK : 'loginaslink',
        FULLNAME : 'fullname',
        EXTRAFIELDS : 'extrafields',
        OPTIONS : 'options',
        ODD  : 'odd',
        EVEN : 'even',
        HIDDEN : 'hidden',
        SEARCH : 'ulp-search',
        CLOSE : 'close',
        CLOSEBTN : 'close-button'
    };
    var create = Y.Node.create;

    var USERLOGINNER = function(config) {
        USERLOGINNER.superclass.constructor.apply(this, arguments);
    };
    Y.extend(USERLOGINNER, Y.Base, {
        _searchTimeout : null,
        _loadingNode : null,
        _escCloseEvent : null,
        initializer : function(config) {
            this.set(ULP.BASE, create('<div class="' + CSS.PANEL + ' ' + CSS.HIDDEN + '"></div>')
                .append(create('<div class="' + CSS.WRAP + '"></div>')
                    .append(create('<div class="' + CSS.HEADER + ' header"></div>')
                        .append(create('<div class="' + CSS.CLOSE + '"></div>'))
                        .append(create('<h2>' + M.str.local_loginas.loginasuser + '</h2>'))
                    )
                    .append(create('<div class="' + CSS.CONTENT + '"></div>')
                        .append(create('<div class="' + CSS.AJAXCONTENT + '"></div>'))
                        .append(create('<div class="' + CSS.LIGHTBOX + ' ' + CSS.HIDDEN + '"></div>')
                            .append(create('<img alt="loading" class="' + CSS.LOADINGICON + '" />')
                                .setAttribute('src', M.util.image_url('i/loading', 'moodle')))
                            .setStyle('opacity', 0.5))
                    )
                    .append(create('<div class="' + CSS.FOOTER + '"></div>')
                        .append(create('<div class="' + CSS.SEARCHBUTTONS + ' clearfix"></div>'))
                        .append(create('<div class="' + CSS.SEARCH + '"><label>' + M.str.moodle.search + '</label></div>')
                            .append(create('<input type="text" id="loginasusersearch" value="" />'))
                        )
                    )
                )
            );

            this.set(ULP.SEARCH, this.get(ULP.BASE).one('#loginasusersearch'));
            Y.all('.local_loginas_plugin input').each(function(node){
                if (node.getAttribute('type', 'submit')) {
                    node.on('click', this.show, this);
                }
            }, this);
            // Add event to the settings link
            if (Y.one('.local_loginas_setting_link a')) {
                Y.one('.local_loginas_setting_link a').on('click', this.show, this);
            };

            this.get(ULP.BASE).one('.' + CSS.HEADER + ' .' + CSS.CLOSE).on('click', this.hide, this);
            this._loadingNode = this.get(ULP.BASE).one('.' + CSS.CONTENT + ' .' + CSS.LIGHTBOX);
            var params = this.get(ULP.PARAMS);
            params['id'] = this.get(ULP.COURSEID);
            this.set(ULP.PARAMS, params);

            Y.on('key', this.preSearch, this.get(ULP.SEARCH), 'down:13', this);

            Y.one(document.body).append(this.get(ULP.BASE));

            var base = this.get(ULP.BASE);
            base.plug(Y.Plugin.Drag);
            base.dd.addHandle('.' + CSS.HEADER + ' h2');
            base.one('.' + CSS.HEADER + ' h2').setStyle('cursor', 'move');
        },
        preSearch : function(e) {
            this.search(null, 0);
            /*
            var value = this.get(ULP.SEARCH).get('value');
            if (value.length < 3 || value == this.get(ULP.LASTSEARCH)) {
                return;
            }
            this.set(ULP.LASTSEARCH, value);
            if (this._searchTimeout) {
                clearTimeout(this._searchTimeout);
                this._searchTimeout = null;
            }
            var self = this;
            this._searchTimeout = setTimeout(function(){
                self._searchTimeout = null;
                self.search(null, false);
            }, 300);
            */
        },
        show : function(e) {
            e.preventDefault();
            e.halt();

            var base = this.get(ULP.BASE);
            base.removeClass(CSS.HIDDEN);
            var x = (base.get('winWidth') - 400) / 2;
            var y = (parseInt(base.get('winHeight')) - base.get('offsetHeight')) / 2 + parseInt(base.get('docScrollY'));
            if (y < parseInt(base.get('winHeight')) * 0.1) {
                y = parseInt(base.get('winHeight')) * 0.1;
            }
            base.setXY([x,y]);

            if (this.get(ULP.USERS) === null) {
                this.search(e, false);
            }

            this._escCloseEvent = Y.on('key', this.hide, document.body, 'down:27', this);
        },
        hide : function(e) {
            if (this._escCloseEvent) {
                this._escCloseEvent.detach();
                this._escCloseEvent = null;
            }
            this.get(ULP.BASE).addClass(CSS.HIDDEN);
            if (this.get(ULP.REQUIREREFRESH)) {
                window.location = this.get(ULP.URL);
            }
        },
        search : function(e, go) {
            if (e) {
                e.halt();
                e.preventDefault();
            }
            var on, params;

            var gopage = go == 0 ? 0 : this.get(ULP.PAGE) + go;
            this.set(ULP.USERCOUNT, gopage * 25);
            this.set(ULP.PAGE, gopage);

            params = this.get(ULP.PARAMS);
            params['sesskey'] = M.cfg.sesskey;
            params['action'] = 'searchusers';
            params['search'] = this.get(ULP.SEARCH).get('value');
            params['page'] = this.get(ULP.PAGE);

            Y.io(M.cfg.wwwroot + this.get(ULP.AJAXURL), {
                method: 'POST',
                data: build_querystring(params),
                on: {
                    start: this.displayLoading,
                    complete: this.processSearchResults,
                    end: this.removeLoading
                },
                context:this,
                arguments: {
                    contextid:params['contextid']
                }
            });
        },
        displayLoading : function() {
            this._loadingNode.removeClass(CSS.HIDDEN);
        },
        removeLoading : function() {
            this._loadingNode.addClass(CSS.HIDDEN);
        },
        processSearchResults : function(tid, outcome, args) {
            try {
                var result = Y.JSON.parse(outcome.responseText);
                if (result.error) {
                    return new M.core.ajaxException(result);
                }
            } catch (e) {
                new M.core.exception(e);
            }
            if (!result.success) {
                this.setContent = M.str.local_loginas.errajaxsearch;
            }
            var users;
            if (!args.append) {
                users = create('<div class="' + CSS.USERS + '"></div>');
            } else {
                users = this.get(ULP.BASE).one('.' + CSS.SEARCHRESULTS + ' .' + CSS.USERS);
            }
            var count = this.get(ULP.PAGE) * 25;
            for (var i in result.response.users) {
                count++;
                var user = result.response.users[i];
                params = [];
                params['id'] = this.get(ULP.COURSEID);
                params['sesskey'] = M.cfg.sesskey;
                params['user'] = user.id;
                var loginasurl = M.cfg.wwwroot + '/course/loginas.php?' + build_querystring(params);

                users.append(create('<div class="' + CSS.USER + ' clearfix" rel="' + user.id + '"></div>')
                    .addClass((count % 2) ? CSS.ODD : CSS.EVEN)
                    .append(create('<div class="' + CSS.COUNT + '">' + count + '</div>'))
                    .append(create('<div class="' + CSS.PICTURE + '"></div>')
                        .append(create(user.picture)))
                    .append(create('<div class="' + CSS.DETAILS + '"></div>')
                        .append(create('<a class="' + CSS.LOGINASLINK + '" href="' + loginasurl + '"></a>')
                            .append(create('<div class="' + CSS.FULLNAME + '">' + user.fullname + '</div>'))
                        )
                        .append(create('<div class="' + CSS.EXTRAFIELDS + '">' + user.extrafields + '</div>'))
                    )
                );
            }
            this.set(ULP.USERCOUNT, count);

            var usersstr = (result.response.totalusers == '1') ? M.str.local_loginas.ajaxoneuserfound : M.util.get_string('ajaxxusersfound','local_loginas', result.response.totalusers);
            var content = create('<div class="' + CSS.SEARCHRESULTS + '"></div>')
                .append(create('<div class="' + CSS.TOTALUSERS + '">' + usersstr + '</div>'))
                .append(users);

            var searchbuttons = this.get(ULP.BASE).one('.' + CSS.SEARCHBUTTONS);

            $currentpage = this.get(ULP.PAGE);
            if ($currentpage && !this.get(ULP.BASE).one('.' + CSS.PREVRESULTS)) {
                var prevres = create('<div class="' + CSS.PREVRESULTS + '"><a href="#">' + M.str.local_loginas.ajaxprev25 + '</a></div>');
                prevres.on('click', this.search, this, -1);
                searchbuttons.append(prevres);
            }

            if (!$currentpage && this.get(ULP.BASE).one('.' + CSS.PREVRESULTS)) {
                this.get(ULP.BASE).one('.' + CSS.PREVRESULTS).remove();
            }

            if (result.response.totalusers > ($currentpage + 1) * 25) {
                if (!this.get(ULP.BASE).one('.' + CSS.NEXTRESULTS)) {
                    var nextres = create('<div class="' + CSS.NEXTRESULTS + '"><a href="#">' + M.str.local_loginas.ajaxnext25 + '</a></div>');
                    nextres.on('click', this.search, this, 1);
                    searchbuttons.append(nextres);
                }
            } else if (this.get(ULP.BASE).one('.' + CSS.NEXTRESULTS)) {
                this.get(ULP.BASE).one('.' + CSS.NEXTRESULTS).remove();
            }
            this.setContent(content);

        },
        setContent: function(content) {
            this.get(ULP.BASE).one('.' + CSS.CONTENT + ' .' + CSS.AJAXCONTENT).setContent(content);
        }
    }, {
        NAME : ULP.NAME,
        ATTRS : {
            url : {
                validator : Y.Lang.isString
            },
            ajaxurl : {
                validator : Y.Lang.isString
            },
            base : {
                setter : function(node) {
                    var n = Y.one(node);
                    if (!n) {
                        Y.fail(ULP.NAME + ': invalid base node set');
                    }
                    return n;
                }
            },
            users : {
                validator : Y.Lang.isArray,
                value : null
            },
            courseid : {
                value : null
            },
            params : {
                validator : Y.Lang.isArray,
                value : []
            },
            instances : {
                validator : Y.Lang.isArray,
                setter : function(instances) {
                    var i, ia = [], count = 0;
                    for (i in instances) {
                        ia.push(instances[i]);
                        count++;
                    }
                    this.set(ULP.MULTIPLE, (count > 1));
                }
            },
            multiple : {
                validator : Y.Lang.isBool,
                value : false
            },
            page : {
                validator : Y.Lang.isNumber,
                value : 0
            },
            userCount : {
                value : 0,
                validator : Y.Lang.isNumber
            },
            requiresRefresh : {
                value : false,
                validator : Y.Lang.isBool
            },
            search : {
                setter : function(node) {
                    var n = Y.one(node);
                    if (!n) {
                        Y.fail(ULP.NAME + ': invalid search node set');
                    }
                    return n;
                }
            },
            lastPreSearchValue : {
                value : '',
                validator : Y.Lang.isString
            },
            strings  : {
                value : {},
                validator : Y.Lang.isObject
            }
        }
    });
    Y.augment(USERLOGINNER, Y.EventTarget);

    M.local_loginas = M.local_loginas || {};
    M.local_loginas.quickloginas = {
        init : function(cfg) {
            new USERLOGINNER(cfg);
        }
    }

}, '@VERSION@', {requires:['base','node', 'overlay', 'io-base', 'test', 'json-parse', 'event-delegate', 'dd-plugin', 'event-key']});
