/*! pimpmylog - 1.0.5 - 304e44fae52b81256e7624dbca2a9cb3d005808e*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
var file, notification, auto_refresh_timer, fingerprint, first_launch, file_size, last_line, loading, reset, notification_displayed = !1,
    query_parameters = function () {
        for (var a, b = [], c = window.location.href.slice(window.location.href.indexOf("?") + 1).split("&"), d = 0; d < c.length; d++) a = c[d].split("="), b.push(a[0]), b[a[0]] = a[1];
        return b
    }(),
    reload_page = function (a) {
        "use strict";
        a = "undefined" != typeof a ? a : !1;
        var b = window.location.href.split("?")[0] + "?" + $.param({
            tz: $("select#cog-tz").val(),
            l: $("select#cog-lang").val(),
            w: $("#cog-wide").data("value"),
            i: file,
            m: $("#max").val(),
            r: $("#autorefresh").val(),
            s: $("#search").val(),
            n: notification
        });
        a === !1 ? document.location.href = b : window.history.pushState({
            pageTitle: document.title
        }, "", b)
    },
    set_auto_refresh = function (a) {
        "use strict";
        $("#autorefresh").val(a)
    },
    set_max = function (a) {
        "use strict";
        $("#max").val(a)
    },
    set_title = function () {
        "use strict";
        document.title = title_file.replace("%i", file).replace("%f", files[file].display)
    },
    notification_class = "warning",
    set_notification = function (a) {
        "use strict";
        void 0 === a && (a = notification), a === !0 ? ($("#notification").removeClass("btn-warning btn-success btn-danger").addClass("active btn-" + notification_class), notification = !0) : ($("#notification").removeClass("btn-warning btn-success btn-danger active"), notification = !1)
    },
    is_notification = function () {
        "use strict";
        return $("#notification").hasClass("active")
    },
    notify = function (a, b) {
        "use strict";
        if ("webkitNotifications" in window) {
            var c = window.webkitNotifications.checkPermission();
            if (0 === c) {
                if (notification_class = "success", set_notification(), notification === !0 && void 0 !== a && notification_displayed === !1) {
                    notification_displayed = !0;
                    var d = window.webkitNotifications.createNotification("img/icon72.png", a, b);
                    d.onclick = function () {
                        window.focus(), d.close()
                    }, d.onclose = function () {
                        notification_displayed = !1
                    }, d.show(), setTimeout(function () {
                        try {
                            d.close()
                        } catch (a) {}
                    }, 5e3)
                }
            } else 2 === c ? (notification_class = "danger", set_notification()) : (notification_class = "warning", set_notification(), window.webkitNotifications.requestPermission(function () {
                notify(a, b)
            }))
        } else if ("Notification" in window)
            if ("default" === window.Notification.permission) notification_class = "warning", set_notification(), window.Notification.requestPermission(function () {
                notify(a, b)
            });
            else if ("granted" === window.Notification.permission) {
            if (notification_class = "success", set_notification(), notification === !0 && void 0 !== a && notification_displayed === !1) {
                notification_displayed = !0;
                var e = new window.Notification(a, {
                    body: b,
                    tag: "Pimp My Log"
                });
                e.onclick = function () {
                    this.close()
                }, e.onclose = function () {
                    notification_displayed = !1
                }
            }
        } else if ("denied" === window.Notification.permission) return notification_class = "danger", set_notification(), void 0
    },
    pml_alert = function (a, b) {
        "use strict";
        $('<div class="alert alert-' + b + ' alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + a + "</div>").appendTo("#notice")
    },
    pml_singlealert = function (a, b) {
        "use strict";
        $("#singlenotice").html('<div class="alert alert-' + b + ' alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + a + "</div>")
    },
    type_parser = function (a) {
        "use strict";
        var b = "txt",
            c = "",
            d = 0;
        if (void 0 !== a) {
            var e = a.split("/");
            void 0 !== e[1] ? (d = parseInt(a.split("/").slice(-1), 10), b = a.split("/").slice(0, -1).join("/")) : b = a, e = b.split(":"), void 0 !== e[1] && (c = b.split(":").slice(1).join(":"), b = e[0])
        }
        return {
            parser: b,
            param: c,
            cut: d
        }
    },
    val_cut = function (a, b) {
        "use strict";
        return void 0 === b ? a : 0 === b ? a : a.length <= Math.abs(b) ? a : b > 0 ? a.substr(0, b) + "&hellip;" : "&hellip;" + a.substr(b)
    },
    get_logs = function (load_default_values, load_full_file, load_from_get) {
        "use strict";
        var wanted_lines;
        if (null !== auto_refresh_timer && (clearTimeout(auto_refresh_timer), auto_refresh_timer = null), load_default_values === !0) {
            if (load_from_get === !0) {
                var found;
                "true" === query_parameters.n ? set_notification(!0) : "false" === query_parameters.n ? set_notification(!1) : set_notification(files[file].notify), found = files[file].max, void 0 !== typeof query_parameters.m && $("#max option").each(function () {
                    this.value === query_parameters.m && (found = query_parameters.m)
                }), set_max(found), found = files[file].refresh, void 0 !== typeof query_parameters.r && $("#autorefresh option").each(function () {
                    this.value === query_parameters.r && (found = query_parameters.r)
                }), set_auto_refresh(found)
            } else set_max(files[file].max), set_auto_refresh(files[file].refresh), set_notification(files[file].notify);
            load_full_file = !0
        } else load_default_values = !1;
        reload_page(!0), load_full_file === !0 ? (reset = 1, file_size = 0, last_line = "") : (reset = 0, load_full_file = !1), $(".loader").toggle(), loading = !0, wanted_lines = $("#max").val(), $.ajax({
            url: "inc/getlog.pml.php?" + (new Date).getTime() + "&" + querystring,
            data: {
                ldv: load_default_values,
                file: file,
                filesize: file_size,
                max: wanted_lines,
                search: $("#search").val(),
                csrf_token: csrf_token,
                lastline: last_line,
                reset: reset
            },
            type: "POST",
            dataType: "json"
        }).fail(function (a) {
            return $(".loader").toggle(), loading = !1, a.error ? ($(".result").hide(), $("#error").show(), $("#errortxt").html(a.responseText), notify(notification_title.replace("%i", file).replace("%f", files[file].display), lemma.error), void 0) : void 0
        }).done(function (logs) {
            if ($(".loader").toggle(), loading = !1, file_size = logs.newfilesize, last_line = logs.lastline, logs.error) return $(".result").hide(), $("#error").show(), $("#errortxt").html(logs.error), notify(notification_title.replace("%i", file).replace("%f", files[file].display), lemma.error), void 0;
            if (logs.warning && pml_alert(logs.warning, "warning"), logs.notice && pml_alert(logs.notice, "info"), logs.singlewarning && pml_singlealert(logs.singlewarning, "warning"), logs.singlenotice && pml_singlealert(logs.singlenotice, "info"), $("#error").hide(), $(".result").show(), logs.full)
                if (logs.found === !1) {
                    var nolog = lemma.no_log;
                    "" !== logs.search && (nolog = logs.regsearch ? lemma.search_no_regex.replace("%s", "<code>" + logs.search + "</code>") : lemma.search_no_regular.replace("%s", "<code>" + logs.search + "</code>")), $("#nolog").html(nolog).show(), $("#logshead").hide()
                } else $("#nolog").text("").hide(), $("#logshead").show();
            else logs.logs && ($("#nolog").text("").hide(), $("#logshead").show());
            logs.regsearch ? ($("#searchctn").addClass("has-success"), $("#searchctn").prop("title", lemma.regex_valid)) : ($("#searchctn").removeClass("has-success"), $("#searchctn").prop("title", lemma.regex_invalid)), $(function () {
                if (logs.headers) {
                    $("#logshead").text("");
                    var a = $("<tr>").addClass(file);
                    for (var b in logs.headers) $("<th>" + logs.headers[b] + "</th>").addClass(b).appendTo(a);
                    a.appendTo("#logshead")
                }
            }), logs.full && $("#logsbody").text(""), $("#logsbody tr").removeClass("newlog");
            var uaparser = new UAParser,
                rowidx = 0,
                rows = [];
            for (var log in logs.logs) {
                var tr = $("<tr>").addClass(file).data("log", logs.logs[log].pml);
                for (var c in logs.logs[log])
                    if ("pml" !== c) {
                        var type = type_parser(files[file].format.types[c]),
                            val = logs.logs[log][c],
                            title = val;
                        if ("-" === val && (val = ""), "uaw3c" === type.parser && (type.parser = "ua", val = val.replace(/\+/g, " ")), "badge" === type.parser) {
                            var clas;
                            "http" === type.param ? clas = badges[type.param][logs.logs[log][c].substr(0, 1)] : "severity" === type.param && (clas = badges[type.param][logs.logs[log][c].toLowerCase()], void 0 === clas && (clas = badges[type.param][logs.logs[log][c]])), void 0 === clas && (clas = "default"), val = '<span class="label label-' + clas + '">' + val_cut(val, type.cut) + "</span>"
                        } else if ("date" === type.parser) title = logs.logs[log].pml, val = '<div class="nozclip" style="position: relative;">' + val_cut(val, type.cut) + "</div>";
                        else if ("numeral" === type.parser) "" !== val && "" !== type.param && (val = numeral(val).format(type.param));
                        else if ("ip" === type.parser) val = "geo" === type.param ? '<a href="' + geoip_url.replace("%p", val) + '" target="linkout">' + val_cut(val, type.cut) + "</a>" : '<a href="' + type.param + "://" + val + '" target="linkout">' + val_cut(val, type.cut) + "</a>";
                        else if ("link" === type.parser) val = '<a href="' + val + '" target="linkout">' + val_cut(val, type.cut) + "</a>";
                        else if ("ua" === type.parser) {
                            var ua = uaparser.setUA(val).getResult(),
                                uas = type.param.match(/\{[a-zA-Z.]*\}/g),
                                uaf = !1;
                            for (var k in uas) {
                                var a;
                                try {
                                    a = eval("ua." + uas[k].replace("{", "").replace("}", "")), void 0 === a && (a = "")
                                } catch (e) {
                                    a = ""
                                }
                                "" !== a && (uaf = !0, type.param = type.param.replace(uas[k], a))
                            }
                            uaf === !0 && (val = $.trim(type.param))
                        } else val = val_cut(val, type.cut);
						var abc = "" ;
						if (c=='Log')
							abc = "onclick=function(){alert('1')}" ;
                        $("<td "+abc+">" + val + "</td>").prop("title", title).addClass("pml-" + c + " pml-" + type.parser).appendTo(tr)
                    }
                logs.full || (tr.addClass("newlog"), rowidx++), rows.push(tr)
            }
            if (logs.full) $("#logsbody").append(rows);
            else {
                $("#logsbody").prepend(rows);
                var rowd = $("#logsbody tr").length;
                rowd > wanted_lines && (rowd -= wanted_lines, $("#logsbody").find("tr:nth-last-child(-n+" + rowd + ")").remove())
            }
            var rowct = "",
                rowc = $("#logsbody tr").length;
            1 === rowc ? rowct = lemma.display_log + " " : rowc > 1 && (rowct = lemma.display_nlogs.replace("%s", rowc) + " "), $("#footer").html(rowct + logs.footer), first_launch === !1 && (logs.full ? logs.fingerprint !== fingerprint && (notify(notification_title.replace("%i", file).replace("%f", files[file].display), lemma.new_logs), fingerprint = logs.fingerprint) : 1 === rowidx ? notify(notification_title.replace("%i", file).replace("%f", files[file].display), lemma.new_log) : rowidx > 1 && notify(notification_title.replace("%i", file).replace("%f", files[file].display), lemma.new_nlogs.replace("%s", rowidx))), first_launch = !1;
            var i = Math.max(0, parseInt($("#autorefresh").val(), 10));
            i > 0 && (auto_refresh_timer = setTimeout(function () {
                get_logs()
            }, 1e3 * i))
        }).always(function () {})
    };
$(function () {
    "use strict";

    function a(a) {
        return a ? "addClass" : "removeClass"
    }
    "bs" === file_selector ? ($(".file_menup.active").length || $(".file_menup:first").addClass("active"), $("#file_selector").text($(".file_menup.active a").text()), file = $(".file_menup.active").data("file"), set_title(), $(".file_menu").click(function () {
        $("#file_selector").text($(this).text()), $(".file_menup").removeClass("active"), $(this).parent().addClass("active"), file = $(this).parent().data("file"), set_title(), get_logs(!0)
    })) : (file = $("#file_selector_big").val(), set_title(), $("#file_selector_big").change(function () {
        file = $("#file_selector_big").val(), set_title(), get_logs(!0)
    })), $(".logo").click(function () {
        document.location.href = "?"
    }), $("#refresh").click(function () {
        notify(), get_logs()
    }), $(".cog").click(function () {
        switch ($(this).data("cog")) {
        case "wideview":
            "on" === $(this).data("value") ? ($(this).data("value", "off"), $(this).find(".cogon").hide(), $(this).find(".cogoff").show(), $(".tableresult").removeClass("containerwide").addClass("container")) : ($(this).data("value", "on"), $(this).find(".cogoff").hide(), $(this).find(".cogon").show(), $(".tableresult").addClass("containerwide").removeClass("container"))
        }
        reload_page(!0)
    }), $(".cog").each(function () {
        "on" === $(this).data("value") ? ($(this).find(".cogon").show(), $(this).find(".cogoff").hide(), $(".tableresult").addClass("containerwide").removeClass("container")) : ($(this).find(".cogon").hide(), $(this).find(".cogoff").show(), $(".tableresult").addClass("container").removeClass("containerwide"))
    }), $("#cog-lang").change(function () {
        reload_page()
    }), $("#cog-tz").change(function () {
        reload_page()
    }), $(document).keypress(function (a) {
        $(a.target).is("input, textarea") || (114 === a.which ? (notify(), get_logs()) : (102 === a.which || 47 === a.which) && (a.preventDefault(), $("#search").focus()))
    }), $(document).on("input", ".clearable", function () {
        $(this)[a(this.value)]("x")
    }).on("mousemove", ".x", function (b) {
        $(this)[a(this.offsetWidth - 18 < b.clientX - this.getBoundingClientRect().left)]("onX")
    }).on("click", ".onX", function () {
        $(this).removeClass("x onX").val("")
    }), "" !== $("#search.clearable").val() && $("#search.clearable")[a($("#search.clearable").val())]("x"), $("#search").blur(function () {
        get_logs(!1, !0)
    }), $(document).keydown(function (a) {
        return 13 === a.keyCode ? ($("#search").is(":focus") && ($("#search").blur(), get_logs(!1, !0)), a.preventDefault(), !1) : void 0
    }), set_auto_refresh(logs_refresh_default), $("#autorefresh").change(function () {
        get_logs()
    }), set_max(logs_max_default), $("#max").change(function () {
        get_logs(!1, !0)
    }), ("Notification" in window || "webkitNotifications" in window) && ($("#notification").show(), set_notification(notification_default)), $("#notification").click(function () {
        $(this).hasClass("btn-warning") ? notify() : $(this).hasClass("btn-danger") ? pml_alert(lemma.notification_deny, "danger") : set_notification(!is_notification()), reload_page(!0)
    }), notify(), pull_to_refresh === !0 && $("#hook").hook({
        dynamic: !1,
        reloadPage: !1,
        reloadEl: function () {
            notify(), get_logs()
        }
    }), get_logs(!0, !0, !0), $.ajax({
        url: "inc/upgrade.pml.php?" + (new Date).getTime() + "&" + querystring,
        dataType: "json",
        data: {
            csrf_token: csrf_token
        },
        type: "POST"
    }).done(function (a) {
        $("#upgradefooter").html(" - " + a.footer);
        var b = $.cookie("upgradehide");
        b !== a.to && ($("#upgrademessage").html(a.alert), $("#upgradestop").click(function () {
            $.cookie("upgradehide", $(this).data("version")), $("#upgradealert").alert("close")
        }))
    })
});