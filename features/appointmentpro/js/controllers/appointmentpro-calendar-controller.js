/**
 * appointmentpro calendar version 1 controllers
 */
angular
    .module('starter')
    .controller('AppointmentproCalendarController', function (Dialog, Loader, Application, $ionicSideMenuDelegate, Customer,
                                             $session, Modal, $ionicScrollDelegate, $timeout, $ionicModal, 
                                             $ionicHistory, $translate, $rootScope, Appointmentpro, $scope, $state, $stateParams, $controller, $compile) {
    angular.extend(this, $controller('AppointmentproCommanController', {
        Dialog: Dialog,
        $rootScope: $rootScope,
        $scope: $scope,
        $stateParams: $stateParams,
        Appointmentpro: Appointmentpro
    }));
    
    $scope.value_id = Appointmentpro.value_id = $stateParams.value_id;
    $scope.settings = Appointmentpro.getAppointmentproSettings();
    $scope.provider_id = $stateParams.provider_id;
    $scope.payout = {};

    /*Cart Update*/
    $scope.cart = Appointmentpro.cart;
    $scope.cart.provider_id = $scope.provider_id;
    $scope.cart.is_class = false;
       
    var d = new Date();
    var present_month = d.getMonth();
    var present_date = d.getDate();

    function calendari(widget, data) {

        var month = [
            $translate.instant("January", "appointmentpro"),
            $translate.instant("February", "appointmentpro"),
            $translate.instant("March", "appointmentpro"),
            $translate.instant("April", "appointmentpro"),
            $translate.instant("May", "appointmentpro"),
            $translate.instant("June", "appointmentpro"),
            $translate.instant("July", "appointmentpro"),
            $translate.instant("August", "appointmentpro"),
            $translate.instant("September", "appointmentpro"),
            $translate.instant("October", "appointmentpro"),
            $translate.instant("November", "appointmentpro"),
            $translate.instant("December", "appointmentpro")
        ];
        var days = [
            $translate.instant("Sunday", "appointmentpro"),
            $translate.instant("Monday", "appointmentpro"),
            $translate.instant("Tuesday", "appointmentpro"),
            $translate.instant("Wednesday", "appointmentpro"),
            $translate.instant("Thursday", "appointmentpro"),
            $translate.instant("Friday", "appointmentpro"),
            $translate.instant("Saturday", "appointmentpro")
        ];
        var days_abr = [
            $translate.instant("Sun", "appointmentpro"),
            $translate.instant("Mon", "appointmentpro"),
            $translate.instant("Tue", "appointmentpro"),
            $translate.instant("Wed", "appointmentpro"),
            $translate.instant("Thu", "appointmentpro"),
            $translate.instant("Fri", "appointmentpro"),
            $translate.instant("Sat", "appointmentpro")
        ];

        Number.prototype.pad = function(num) {
            var str = '';
            for (var i = 0; i < (num - this.toString().length); i++)
                str += '0';
            return str += this.toString();
        };

        var original = widget.getElementsByClassName('active')[0];

        if (typeof original === 'undefined') {
            original = document.createElement('table');
            original.setAttribute('data-actual',
                data.getFullYear() + '/' +
                data.getMonth() + '/' +
                data.getDate());
            widget.appendChild(original);
        }
        var diff = data - new Date(original.getAttribute('data-actual'));
        diff = new Date(diff).getMonth();
        var e = document.createElement('table');

        e.addclass = 'table table-bordered table-fixed monthview-datetable';
        e.className = diff === 0 ? 'hide-left' : 'hidden-right';
        e.innerHTML = '';

        widget.appendChild(e);
        e.setAttribute('data-actual',
            data.getDate().pad(2) + '/' +
            (data.getMonth()).pad(2) + '/' +
            data.getFullYear());

        var row = document.createElement('tr');
        var title = document.createElement('th');

        title.setAttribute('colspan', 7);

        var boto_prev = document.createElement('button');
        boto_prev.className = 'boto-prev';
        boto_prev.innerHTML = '&#9666;';

        var boto_next = document.createElement('button');
        boto_next.className = 'boto-next';
        boto_next.innerHTML = '&#9656;';

        title.appendChild(boto_prev);
        title.appendChild(document.createElement('span')).innerHTML =
            month[data.getMonth()] + '<span class="any">' + data.getFullYear() + '</span>';

        title.appendChild(boto_next);

        boto_prev.onclick = function() {
            data.setMonth(data.getMonth() - 1);
            calendari(widget, data);
        };

        boto_next.onclick = function() {
            data.setMonth(data.getMonth() + 1);
            calendari(widget, data);
        };

        row.appendChild(title);
        e.appendChild(row);

        row = document.createElement('tr');

        for (var i = 1; i < 7; i++) {
            row.innerHTML += '<th><small>' + days_abr[i] + '</small></th>';
        }

        row.innerHTML += '<th><small>' + days_abr[0] + '</small></th>';
        e.appendChild(row);

        var cal_mes = new Date(data.getFullYear(), data.getMonth(), -1).getDay();
        if (cal_mes == 6) {
            var actual = new Date(data.getFullYear(),
                data.getMonth());
        } else {
            var actual = new Date(data.getFullYear(),
                data.getMonth(), -cal_mes);
        }
        for (var s = 0; s < 6; s++) {
            var row = document.createElement('tr');
            for (var d = 1; d < 8; d++) {
                var cell = document.createElement('td');
                var span = document.createElement('small');

                cell.appendChild(span);
                cell.setAttribute('id',
                    actual.getDate().pad(2) + '/' +
                    (actual.getMonth() + 1).pad(2) + '/' +
                    actual.getFullYear()
                );
                cell.setAttribute('class',
                    actual.getDate().pad(2) + '-' +
                    (actual.getMonth() + 1).pad(2) + '-' +
                    actual.getFullYear()
                );

                var full_date = actual.getDate().pad(2) + '/' + (actual.getMonth() + 1).pad(2) + '/' + actual.getFullYear();
                $scope.date = full_date;


                if (actual.getMonth() !== data.getMonth()) {
                    cell.className = 'text-muted';
                } else {
                    span.innerHTML = actual.getDate();
                    if ((data.getDate() > actual.getDate()) && (actual.getMonth() == present_month)) {
                        cell.className = 'previous';
                    } else {
                        cell.setAttribute('ng-click', 'selectDate("' + full_date + '")');
                    }
                }
                if (actual.getDate() == present_date && actual.getMonth() == present_month)
                    cell.className = 'today';

                actual.setDate(actual.getDate() + 1);
                $compile(cell)($scope);
                row.appendChild(cell);
            }
            e.appendChild(row);
            if (actual.getMonth() !== data.getMonth() && (actual.getMonth() > data.getMonth())) {
                break;
            }
        }
        setTimeout(function() {
            e.className = 'active';
            original.className +=
                diff === 0 ? ' hidden-right' : ' hide-left';
        }, 20);
        original.className = 'inactive';

        setTimeout(function() {
            var inactives = document.getElementsByClassName('inactive');
            for (var i = 0; i < inactives.length; i++)
                widget.removeChild(inactives[i]);
       });
    }


    $scope.loadContent = function () {
         $timeout(function() { calendari(document.getElementById('calendar'), new Date()); });
    };
    
    // When entering the feature, we check the origin!
    $scope.loadContent();


    /** Utility methods */
    function removeClassById(id, klass) {
        document.getElementById(id).classList.remove(klass);
    }

    function addClassById(id, klass) {
        document.getElementById(id).classList.add(klass);
    }

    function removeClassByClass(klass, css_klass) {
        [].forEach.call(document.getElementsByClassName(klass), function(el) { el.classList.remove(css_klass) });
    }

    function addClassByClass(klass, css_klass) {
        [].forEach.call(document.getElementsByClassName(klass), function(el) { el.classList.add(css_klass) });
    }


    $scope.selectDate = function(date) {  
        console.log('date', date);    
        $scope.cart.slot_date = date;
        $scope.is_loading_time = true;
        removeClassByClass('is-date-selected', "item-divider-custom");
        removeClassByClass('is-date-selected', "item-divider");

        if ($scope.date != '') {
            removeClassByClass($scope.date.split("/").join("-"), "is-date-selected"); 
            removeClassByClass($scope.date.split("/").join("-"), "item-divider");
            removeClassByClass($scope.date.split("/").join("-"), "item-divider-custom");
            addClassByClass(date.split("/").join("-"), "is-date-selected"); 
            addClassByClass(date.split("/").join("-"), "item-divider");
            addClassByClass(date.split("/").join("-"), "item-divider-custom");
        }

        $scope.display_date = date;
        if ($scope.date_format == 'mm/dd/yyyy') {
            var display_date = date.split("/");
            $scope.display_date = display_date[1] + '/' + display_date[0] + '/' + display_date[2];
        }

        Appointmentpro.availabletimeSlot($scope.cart, date).success(function (data) {
            $scope.payout = data.data;
            $scope.morningTime = {};
            $scope.afternoonTime = {};
            $scope.eveningTime = {};
            
            var d = new Date(),
                isToday = false,
                t = 0;
            var dd = ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + d.getFullYear();
            if (date == dd) {
                isToday = true;
                t = (d.getHours() * 3600) + (d.getMinutes() * 60);
            }

            if($scope.payout.status == 'success'){
                for (var key in $scope.payout.data.displayTime) {
                    if (isToday && key < t) {
                        continue;
                    }
                    if (key < 43200) {
                        $scope.morningTime[key] = $scope.payout.data.displayTime[key];
                    }
                    if (key >= 43200 && key < 61200) {
                        $scope.afternoonTime[key] = $scope.payout.data.displayTime[key];
                    }
                    if (key >= 61200) {
                        $scope.eveningTime[key] = $scope.payout.data.displayTime[key];
                    }
                }
            }   
            $scope.is_loading_time = false;    
        }).error(function (error) {
            $scope.is_loading_time = false;  
            Dialog.alert($translate.instant("Error", "appointmentpro") ,error.message , "OK", -1, "appointmentpro");         
        }).finally(function () {          
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });

        return ($scope.showTime ? $scope.showTime = false : $scope.showTime = true);

    };

    $scope.choiceSlotTime = function(k, v) {
        $scope.cart.slot_time = k;
        $scope.cart.slot_display_time = v;
        Appointmentpro.cart = $scope.cart;
        $state.go("appointmentpro-checkout", { value_id: $scope.value_id }, { reload: true } );
    }


});