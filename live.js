function StatsFC_Live(key) {
    this.domain         = 'https://api.statsfc.com';
    this.referer        = '';
    this.key            = key;
    this.competition    = '';
    this.group          = '';
    this.team           = '';
    this.highlight      = '';
    this.goals          = false;
    this.showBadges     = false;
    this.upcoming       = false;

    var $j = jQuery;

    this.display = function(placeholder) {
        if (placeholder.length == 0) {
            return;
        }

        var $placeholder = $j('#' + placeholder);

        if ($placeholder.length == 0) {
            return;
        }

        if (this.referer.length == 0) {
            this.referer = window.location.hostname;
        }

        var $container = $j('<div>').addClass('sfc_live');

        // Store globals variables here so we can use it later.
        var domain      = this.domain;
        var competition = this.competition;
        var highlight   = this.highlight;
        var goals       = this.goals;
        var showBadges  = this.showBadges;

        $j.getJSON(
            domain + '/crowdscores/live.php?callback=?',
            {
                key:            this.key,
                domain:         this.referer,
                competition:    this.competition,
                group:          this.group,
                team:           this.team,
                goals:          this.goals,
                upcoming:       this.upcoming
            },
            function(data) {
                if (data.error) {
                    $container.append(
                        $j('<p>').css('text-align', 'center').append(
                            $j('<a>').attr({ href: 'https://statsfc.com', title: 'Football widgets and API', target: '_blank' }).text('StatsFC.com'),
                            ' – ',
                            data.error
                        )
                    );

                    return;
                }

                var $table = $j('<table>');
                var $thead = $j('<thead>');
                var $tbody = $j('<tbody>');

                $thead.append(
                    $j('<tr>').append(
                        $j('<th>').attr('colspan', 5).text('Live')
                    )
                );

                $j.each(data.matches, function(key, match) {
                    var $row        = $j('<tr>').attr('id', 'sfc_' + match.id);
                    var $home       = $j('<td>').addClass('sfc_team sfc_home sfc_badge_' + match.homepath).text(match.home);
                    var $homeScore  = $j('<td>').addClass('sfc_homeScore').text(match.score[0]);
                    var $awayScore  = $j('<td>').addClass('sfc_awayScore').text(match.score[1]);
                    var $away       = $j('<td>').addClass('sfc_team sfc_away sfc_badge_' + match.awaypath).text(match.away);

                    if (showBadges) {
                        $home.addClass('sfc_badge').css('background-image', 'url(https://api.statsfc.com/kit/' + match.homepath + '.svg)');
                        $away.addClass('sfc_badge').css('background-image', 'url(https://api.statsfc.com/kit/' + match.awaypath + '.svg)');
                    }

                    if (highlight == match.home) {
                        $home.addClass('sfc_highlight');
                    } else if (highlight == match.away) {
                        $away.addClass('sfc_highlight');
                    }

                    $home.prepend(
                        $j('<span>').addClass('sfc_status').text(match.status)
                    );

                    if (competition.length == 0) {
                        $away.append(
                            $j('<span>').addClass('sfc_competition').append(
                                $j('<abbr>').attr('title', match.competition).text(match.competitionkey)
                            )
                        );
                    }

                    $row.append(
                        $home,
                        $homeScore,
                        $j('<td>').addClass('sfc_vs').text('-'),
                        $awayScore,
                        $away
                    );

                    $tbody.append($row);

                    if (goals && match.events.length > 0) {
                        $j.each(match.events, function(key, e) {
                            var $row    = $j('<tr>').addClass('sfc_incident');
                            var $home   = $j('<td>').addClass('sfc_home').attr('colspan', 2).text(e.home);
                            var $minute = $j('<td>').addClass('sfc_vs').text(e.minute + "'");
                            var $away   = $j('<td>').addClass('sfc_away').attr('colspan', 2).text(e.away);

                            if (e.home.length > 0) {
                                $home.addClass('sfc_' + e.type);
                            } else if (e.away.length > 0) {
                                $away.addClass('sfc_' + e.type);
                            }

                            $row.append(
                                $home,
                                $minute,
                                $away
                            );

                            $tbody.append($row);
                        });
                    }
                });

                $table.append($thead, $tbody);

                $container.append($table);

                if (data.customer.attribution) {
                    $container.append(
                        $j('<div>').attr('class', 'sfc_footer').append(
                            $j('<p>').append(
                                $j('<small>').append('Powered by ').append(
                                    $j('<a>').attr({ href: 'https://statsfc.com', title: 'StatsFC – Football widgets', target: '_blank' }).text('StatsFC.com')
                                ).append('. Fan data via ').append(
                                    $j('<a>').attr({ href: 'https://crowdscores.com', title: 'CrowdScores', target: '_blank' }).text('CrowdScores.com')
                                )
                            )
                        )
                    );
                }
            }
        );

        $j('#' + placeholder).append($container);

        setInterval(function() {
            $j.getJSON(domain + '/crowdscores/live-updates.php?callback=?', function(data) {
                $j.each(data, function(match_id, score) {
                    $j('#sfc_' + match_id + ' .sfc_homeScore').text(score[0]);
                    $j('#sfc_' + match_id + ' .sfc_awayScore').text(score[1]);
                    $j('#sfc_' + match_id + ' .sfc_status').text(score[2]);
                });
            });
        }, 60000);
    };
}
