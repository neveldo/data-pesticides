<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $data['title'] ?></title>
    <meta name="description"
          content="Dataviz pollution : Découvrez la carte des pesticides en eaux souterraines en France"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3e4e68">

    <meta property="og:url" content="https://www.data-pesticides.fr"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="<?= $data['title'] ?>"/>
    <meta property="og:description"
          content="Dataviz pollution : Découvrez la carte des pesticides en eaux souterraines en France"/>
    <meta property="og:image" content="https://www.data-pesticides.fr/assets/images/social-share.jpg"/>

    <meta property="twitter:site" content="Data-pesticides.fr"/>
    <meta property="twitter:card" content="summary_large_image"/>
    <meta property="twitter:title"
          content="Dataviz pollution : Découvrez la carte des pesticides en eaux souterraines en France"/>
    <meta property="twitter:image" content="https://www.data-pesticides.fr/assets/images/social-share.jpg"/>

    <link rel="icon" href="favicon.ico"/>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/9.5.4/css/bootstrap-slider.min.css"
          rel="stylesheet"/>

    <link href="assets/css/main.css" rel="stylesheet"/>

</head>
<body>

<div class="modal fade" id="dpmodal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">&nbsp;</h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="moreInfoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">A propos de cette dataviz</h4>
            </div>
            <div class="modal-body">

                <p><img alt="Pesticides" style="float:right;height:120px;width:120px;"
                        src="assets/images/pesticides2.png"/>
                    Un grand nombre de nappes souterraines sont aujourd’hui contaminées par des pesticides utilisés
                    essentiellement en <b>agriculture</b> mais également par les <b>jardiniers amateurs</b> ou encore
                    les gestionnaires de voies de communication.
                </p>
                <p>Des réseaux de mesures et de contrôles surveillent l'évolution des concentrations des produits
                    phytosanitaires dans les nappes d'eau souterraines en France. Cette surveillance des nappes est
                    d'autant plus importante que certaines d'entres-elles sont utilisées pour la <b>production d'eau
                        potable</b>.
                    Cette <b>data-visualisation</b> vous permet d'appréhender les tendances des milliers de données
                    recueillies chaque année par les stations de mesures.

                </p>

                <p>Ce sont aujourd'hui près de <b>2 200 stations</b> de mesures faisant partie du réseau de contrôle de
                    surveillance (RCS) et réseau de contrôle opérationnel (RCO) qui ont en charge la surveillance des
                    pesticides dans les nappes d'eau souterraines.</p>

                <h3>Les pesticides en bref</h3>

                <p><img alt="Pesticides" style="float:left;height:140px;width:100px;padding: 5px;"
                        src="assets/images/pesticides3.png"/>
                    Les pesticides, également appelés "produits phytosanitaires" ou "produits phytopharmaceutiques"
                    rassemblent notamment les insecticides, les fongicides et les herbicides.
                    Ils s'attaquent aux insectes ravageurs, aux champignons, aux "mauvaises herbes" et aux vers
                    parasites.
                    Ils sont majoritairement utilisés en agriculture pour la protection des récoltes mais également pour
                    l’entretien des jardins (collectivités locales, particuliers) ou des infrastructures de transports.
                    Les pesticides peuvent avoir des effets toxiques aigus et/ou chroniques tant sur les écosystèmes,
                    notamment aquatiques, que sur l’homme.
                    Près de 600 pesticides différents sont recherchés dans les différents échantillons d’eau prélevés
                    dans le cadre du suivi de la qualité des eaux souterraines.
                    Il en résulte un volume très important de données. Les substances suivies dans les eaux souterraines
                    sont les substances actives des produits commercialisés, ou leurs résidus de dégradation
                    (métabolites).
                </p>
                <p>
                    Une des spécificités du suivi des pesticides dans les eaux souterraines réside dans le fait que le
                    sous-sol est très souvent constitué d’une superposition de nappes d’eau souterraines plus ou moins
                    indépendantes les unes des autres.
                    Un des enjeux consiste à avoir un aperçu de l’état qualitatif de l’ensemble de ces masses d’eau dont
                    certaines sont mobilisées pour la production d’eau potable.
                </p>
                <p>
                    Les pesticides peuvent être classés par famille chimique, les plus importantes étant les
                    organophosphorés, les organochlorés, les carbamates et les triazines.
                    Ils peuvent également être classés selon leur fonction : herbicides, fongicides, insecticides, etc.
                    <br/>
                    Les seuils maximaux de concentration de produits phytosanitaires dans l’eau destinée à la
                    consommation humaine sont de 0,1 μg/L pour chaque
                    molécule analysée ou 0,5μg/l pour le cumul de toutes les molécules détectées.
                </p>

                <h3>En savoir plus sur les pesticides</h3>

                <ul>
                    <li><a href="http://www.eaufrance.fr/IMG/pdf/campex_201603.pdf" target="_blank">Surveillance des
                            micropolluants dans les milieux aquatiques : des avancées récentes (eaufrance.fr)</a></li>
                    <li><a href="http://www.brgm.fr/sites/default/files/enjeux_geosciences_08.pdf" target="_blank">Pesticides
                            dans les eaux souterraines : comprendre pour mieux prévenir (brgm.fr)</a></li>
                    <li>
                        <a href="http://www.statistiques.developpement-durable.gouv.fr/lessentiel/ar/246/1108/respect-normes-pesticides-eaux-souterraines.html"
                           target="_blank">L'essentiel sur... Les pesticides dans les eaux
                            (developpement-durable.gouv.fr)</a></li>
                    <li>
                        <a href="http://www.eau-seine-normandie.fr/fileadmin/mediatheque/Expert/Etudes_et_Syntheses/etude_2008/Guide_toxique/Guide_pesticides.pdf"
                           target="_blank">Guide pesticides (eau-seine-normandie.fr)</a></li>
                </ul>

                <h3>Méthodologie</h3>
                <p>
                    Cette dataviz s’appuie sur <a
                        href="http://www.donnees.statistiques.developpement-durable.gouv.fr/dataviz_pesticides/"
                        target="_blank">les données</a> provenant du réseau de surveillance des nappes souterraines (RCS
                    et RCO) qui comprend près de 2 200 stations de mesures réparties sur le territoire français
                    (métropole et outre-mer).
                    <br/>Elle exploite également les données de <a href="http://www.sandre.eaufrance.fr/"
                                                                   target="_blank">www.sandre.eaufrance.fr</a> ainsi que
                    <a href="https://www.insee.fr/fr/information/2114819" target="_blank">le référentiel des
                        départements de l'Insee</a>.
                </p>
                <p>
                    Pour chaque station de mesures, plusieurs prélèvements d’eau peuvent être effectués durant une
                    année. Ces prélèvements d’eau sont analysés par un laboratoire agréé.
                    A chaque station est associée une liste de pesticides à surveiller (cette liste est différente d’une
                    station à une autre, les pratiques agricoles, ... actuelles ou passées n’étant pas homogènes sur le
                    territoire métropolitain).
                    Le laboratoire chargé des analyses des échantillons d’eau prélevés a pour mission de mesurer la
                    concentration de chaque pesticide associé à une station de mesure.
                </p>
                <p>
                    Il en résulte des jeux de données contenant les informations sur les concentrations moyennes
                    annuelles des différents pesticides par station de prélèvement et par année.
                    Cette data-visualisation exploite ces données afin de mettre en évidence la répartition géographique
                    et les évolutions temporelles des pesticides en eaux souterraines.
                    Il est ainsi possible de visualiser les moyennes annuelles des concentrations totales en pesticides
                    ou les moyennes par famille de pesticides, par station ou par département, et par année sur une
                    carte interactive.
                    <br/>Un clic sur un département ou une station vous permet d'accéder aux informations détaillées :
                    courbe d'évolution dans le temps, répartition des concentrations par famille, etc.
                    <br/>Le bouton "France" vous permet d'accéder aux chiffres clés pour la France entière : évolution de la moyenne nationale
                    des concentrations relevées dans les stations, nombre de prélèvements effectués, etc.
                </p>

                <p>
                    Pour une station donnée, la concentration totale en pesticides correspond à la somme des
                    concentrations moyennes annuelles de tous les pesticides analysés
                    par la station (issues des analyses quantifiées et non quantifiées<b>*</b>).
                    <br/>Pour une station donnée, la concentration en pesticides appartenants à une famille chimique
                    correspond à la somme des concentrations moyennes annuelles des pesticides analysés par la station
                    qui appartiennent à la famille.
                    <br/>Pour un département donné, la concentration moyenne correspond à la moyenne des valeurs des
                    stations qui appartiennent au département.
                    <br/>La concentration moyenne en France correspond à la moyenne des valeurs de toutes les stations
                    de France.
                    <br/>Les intervalles de la légende de la carte sont séparées par les <a
                        href="https://fr.wikipedia.org/wiki/Quartile" target="_blank">quartiles</a> qui permettent de
                    diviser les données triées en quatre parts égales. Par exemple, la première intervalle correspond aux
                    25% des stations les moins polluées.
                </p>
                <p><b>*</b> : Une analyse non quantifiée signifie que soit le pesticide n’est effectivement pas
                    présent dans l’échantillon, soit l’appareil de mesure du laboratoire n’est pas suffisamment
                    performant pour détecter le pesticide. Dans ce cas, la concentration du pesticide dans
                    l’échantillon est fixée par convention à la moitié de la limite de quantification de l’appareil de
                    mesure du laboratoire.</p>

                <h3>Crédit</h3>

                <p>Cette data-visualisation a été créée par <a href="https://twitter.com/VincentBroute" target="_blank">Vincent
                        Brouté</a> dans le cadre d'un <a
                        href="http://www.developpement-durable.gouv.fr/concours-data-visualisation-sur-pesticides-dans-eaux-souterraines-0"
                        target="_blank">concours</a> organisé par le <a href="http://www.developpement-durable.gouv.fr/"
                                                                        target="_blank">ministère de l'environnement, de
                        l'énergie et de la mer.</a>
                    Elle a été réalisée avec <a href="https://www.blazegraph.com/" target="_blank">BlazeGraph</a>, <a
                        href="http://silex.sensiolabs.org/" target="_blank">Silex</a>, <a
                        href="http://getbootstrap.com/" target="_blank">Bootstrap</a>, <a href="http://fontawesome.io/"
                                                                                          target="_blank">Font
                        Awesome</a>, <a href="http://www.highcharts.com/" target="_blank">Highcharts</a>, <a
                        href="https://jquery.com/" target="_blank">jQuery</a> et <a
                        href="https://www.vincentbroute.fr/mapael/" target="_blank">jQuery Mapael</a>.
                    Les illustrations (<a href="https://creativecommons.org/licenses/by/3.0/us/">licence Creative
                        Commons</a>) ont été créées par <a href="https://thenounproject.com/term/chemical/51106/"
                                                           target="_blank">Luis Prado</a> et <a
                        href="https://thenounproject.com/term/chemical/73158/" target="_blank">iconsmind.com</a>.
                    Les informations de cette page proviennent de différents documents : <a
                        href="http://www.developpement-durable.gouv.fr/sites/default/files/Reglement_Concours_Datavisualisation_Pesticides_dans_eaux_souterraines_15_12_2016_VF.pdf"
                        target="_blank">Concours de data-visualisation des données "pesticides dans les eaux
                        souterraines"</a>, <a href="http://www.brgm.fr/sites/default/files/enjeux_geosciences_08.pdf"
                                              target="_blank">Pesticides dans les eaux souterraines : comprendre pour
                        mieux prévenir (brgm)</a>, <a href="https://fr.wikipedia.org">Wikipédia</a>.
                    <br/>Le code source est disponible sur <a href="https://github.com/neveldo/data-pesticides"
                                                       target="_blank">GitHub</a>.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<div class="wrapper mapcontainer">
    <div class="nav-side-menu">
        <div class="loader-container">
            <div class="loader">Chargement ...</div>
        </div>

        <div class="brand"> Data <i class="fa fa-tint" style="color:#5F9BDB;"></i> Pesticides</div>
        <div class="subbrand">
            Visualisez les teneurs en pesticides dans les eaux souterraines
        </div>

        <i class="fa fa-bars fa-2x toggle-btn" data-toggle="collapse" data-target=".menu-content"></i>

        <div class="menu-list">
            <ul id="menu-content" class="menu-content collapse in">
                <li data-toggle="collapse" data-target="#filter" aria-expanded="true">
                    <i class="fa fa-globe fa-lg"></i> Visualiser les données par <span class="arrow"></span>
                </li>
                <li class="simple">
                    <ul class="sub-menu collapse in" id="filter" aria-expanded="true">
                        <li class="simple" style="text-align:center;">
                            <div class="btn-group filter entityTypeChoice" role="group" aria-label="...">
                                <button type="button" class="btn btn-default on" data-entity-type="Station">Station
                                </button>
                                <button type="button" class="btn btn-default" data-entity-type="Department">
                                    Département
                                </button>
                                <button type="button" class="btn btn-default showCountryModal">France</button>
                            </div>
                        </li>
                    </ul>
                </li>

                <li data-toggle="collapse" data-target="#year" aria-expanded="true">
                    <i class="fa fa-calendar fa-lg"></i> Année <span class="arrow"></span>
                </li>
                <li class="simple">
                    <ul class="sub-menu collapse in" id="year" aria-expanded="true">
                        <li class="simple">
                            <div class="sliderContainer padT10">
                                <span class="minSlider"></span> <input style="display:none;" id="yearSlider"
                                                                       data-slider-id='yearSlider' type="text"/> <span
                                    class="maxSlider"></span>
                                <div class="center" style="padding: 8px;"><span class="sliderValue"></span></div>
                            </div>
                        </li>
                    </ul>
                </li>

                <li data-toggle="collapse" data-target="#pesticideFamilies" aria-expanded="true">
                    <i class="fa fa-flask fa-lg"></i> Familles de pesticides <span class="arrow"></span>
                </li>
                <li class="simple">
                    <div class="families-container">
                        <ul class="sub-menu collapse in pesticideFamilies" id="pesticideFamilies" aria-expanded="true">
                            <li class="on">
                                <a href="#" class="pesticide-map" data-id="allFamilies">Tous</a></li>
                        </ul>
                    </div>
                </li>

                <li data-toggle="collapse" data-target="#legend" aria-expanded="true">
                    <i class="fa fa-flask fa-lg"></i> Concentration en µg/L <span class="arrow"></span>
                </li>
                <li class="simple">
                    <ul class="sub-menu collapse in" id="legend" aria-expanded="true">
                        <li class="simple">
                            <div class="legend padT5"></div>
                            <div class="metadataContainer"></div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="container" id="main">
        <div class="row">
            <div class="col-md-12 col-main">
                <div class="welcome">
                    <div class="inner-welcome">
                        <h3>Explorez les données des pesticides en eaux souterraines !</h3>
                        <p><img style="float:right;height:60px;width:60px;" src="assets/images/pesticides.png"
                                alt="Pesticides"/>
                            Un grand nombre de nappes souterraines sont aujourd’hui contaminées par des pesticides
                            utilisés essentiellement en <b>agriculture</b> mais également par les <b>jardiniers
                                amateurs</b> ou encore les gestionnaires de voies de communication.
                        </p>
                        <p>Des réseaux de mesures et de contrôles surveillent l'évolution des concentrations des
                            produits phytosanitaires dans les nappes d'eau souterraines en France. Cette surveillance
                            des nappes est d'autant plus importante que certaines d'entres-elles sont utilisées pour la
                            <b>production d'eau potable</b>.
                            Cette <b>data-visualisation</b> vous permet d'appréhender les tendances des milliers de
                            données recueillies chaque année par les stations de mesures.

                        </p>
                        <p class="center">
                            <button type="button" class="btn btn-default goButton">C'est parti !</button>
                        </p>
                    </div>
                </div>

                <div class="center main-title">
                    <h2><span class="mapTitle">Concentration totale en pesticides par station de mesures</span></h2>

                    <div class="share-buttons">
                        <a href="#" data-toggle="modal" data-target="#moreInfoModal" title="Plus d'informations"
                           class="on btn btn-default" style="margin-right:10px;"> <span class="fa-lg">?</span> </a>

                        <span class="btn-group filter" role="group">
                            <a title="Partager sur Twitter"
                               href="https://twitter.com/intent/tweet?text=<?= urlencode('Découvrez la carte des pesticides en eaux souterraines en France via @VincentBroute #dataviz #pollution https://www.data-pesticides.fr') ?>"
                               target="_blank" class="on btn btn-default"><i class="fa fa-twitter fa-lg"></i> </a>
                            <a title="Partager sur Facebook" target="_blank"
                               onclick="open('https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fwww.data-pesticides.fr%2F&amp;src=sdkpreparse', 'Facebook', 'resizable=1,height=560,width=600')"
                               class="btn btn-default"><i class="fa fa-facebook fa-lg"></i> </a>
                            <a href="https://github.com/neveldo/data-pesticides" class="btn btn-default"
                               title="Voir le code source sur Github" target="_blank"><i class="fa fa-github fa-lg"></i> </a>
                        </span>
                    </div>

                    <div style="clear:both"></div>
                </div>

                <div class="map"></div>

                <p class="center choose-mode">
                    <span><input type="radio" name="display-mode" id="display-mode-1" value="1"/>Limiter le nombre de stations sur la carte (affichage optimisé)</span>
                    <br/><span><input type="radio" name="display-mode" id="display-mode-2" value="0" checked="checked"/>Voir toutes les stations</span>
                </p>

                <p class="center">Cette data-visualisation a été créée par <a href="https://twitter.com/VincentBroute"
                                                                              target="_blank">Vincent Brouté</a> dans le
                    cadre d'un <a
                        href="http://www.developpement-durable.gouv.fr/concours-data-visualisation-sur-pesticides-dans-eaux-souterraines-0"
                        target="_blank">concours</a> organisé par le <a href="http://www.developpement-durable.gouv.fr/"
                                                                        target="_blank">ministère de l'environnement, de
                        l'énergie et de la mer.</a></p>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="country-modal">
    <h4 class="keyDataTitle"></h4>
    <div class="row">
        <div class="col-md-4">
            <div class="key-data-box median"><i class="fa fa-bar-chart"></i> <span class="number"></span>
                <p class="details"> Médiane des concentrations totales en pesticides par station</p></div>
        </div>
        <div class="col-md-4">
            <div class="key-data-box stationsCount"><i class="fa fa-industry"></i> <span class="number"></span>
                <p class="details">Nombre de stations ayant effectué des analyses</p></div>
        </div>
        <div class="col-md-4">
            <div class="key-data-box analyzesCount"><i class="fa fa-flask"></i> <span class="number"></span>
                <p class="details">Nombre d'analyses de pesticides effectuées</p></div>
        </div>
    </div>
    <h4 class="countryFamiliesGraphTitle"></h4>
    <div class="countryFamiliesGraph"></div>
    <div class="row">
        <div class="col-md-6">
            <h4 class="countryTop5GraphTitle"></h4>
            <div class="countryTop5Graph"></div>
        </div>
        <div class="col-md-6">
            <h4 class="countryBottom5GraphTitle"></h4>
            <div class="countryBottom5Graph"></div>
        </div>
    </div>
    <h4 class="countryTimeGraphTitle"></h4>
    <div class="countryTimeGraph"></div>
    <h4 class="countryStationsGraphTitle"></h4>
    <div class="countryStationsGraph"></div>
    <p>Note : Seules les stations ayant effectué au moins un prélèvement dans l'année sont comptabilisées. </p>
    <p>A partir de 2010, les échantillons d’eau analysés sont issus de prélèvements réalisés dans les stations de
        mesure appartenant au réseau de surveillance RCS/RCO. Avant 2010 (campagnes 2007-2009), les échantillons
        d'eau analysés sont issus de prélèvements réalisés dans un réseau de stations plus important en nombre, ces
        stations ne faisant pas forcément partie du réseau du RCO/RCS.
    </p>
</script>

<script type="text/template" id="entity-headline">
    <p>En <b>%year%</b>, %label%, la %concentrationLabel% était en moyenne de <b>%value% µg/L</b>, soit %comparisonString%.</p>
</script>

<script type="text/template" id="pesticides-in-excess-title">
    L'eau analysée dans cette station était <b><span style="color:darkred;">impropre à la consommation</span></b> en %year% car les concentrations des pesticides suivants dépassaient le seuil maximal de 0,1µg/L :
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/es6-promise/4.0.5/es6-promise.auto.min.js" charset="utf-8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js" charset="utf-8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/5.0.6/highcharts.js" charset="utf-8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mousewheel/3.1.13/jquery.mousewheel.min.js"
        charset="utf-8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.2.0/raphael-min.js" charset="utf-8"></script>
<script src="https://cdn.rawgit.com/neveldo/jQuery-Mapael/master/js/jquery.mapael.js" charset="utf-8"></script>
<script src="https://cdn.rawgit.com/neveldo/jQuery-Mapael/master/js/maps/france_departments.min.js"
        charset="utf-8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/9.5.4/bootstrap-slider.min.js"
        charset="utf-8"></script>

<script src="assets/js/main.js"></script>

<script>
    window.twttr = (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0],
            t = window.twttr || {};
        if (d.getElementById(id)) return t;
        js = d.createElement(s);
        js.id = id;
        js.src = "https://platform.twitter.com/widgets.js";
        fjs.parentNode.insertBefore(js, fjs);

        t._e = [];
        t.ready = function (f) {
            t._e.push(f);
        };

        return t;
    }(document, "script", "twitter-wjs"));
</script>

</body>
</html>
