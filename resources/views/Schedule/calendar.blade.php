@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://uicdn.toast.com/tui-calendar/latest/tui-calendar.css" />
    <style>
        #calendar {
            height: 800px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .tui-full-calendar-weekday-schedule {
            cursor: pointer;
        }

        /* Modal dla szczegółów rezerwacji */
        #eventModal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        #eventModal .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            text-align: left;
            position: relative;
        }
        #eventModal .close {
            position: absolute;
            right: 10px;
            top: 10px;
            font-size: 20px;
            cursor: pointer;
            color: #888;
        }
    </style>
@endsection

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Kalendarz rezerwacji</h1>
        <div id="calendar"></div>
    </div>

    <!-- Modal -->
    <div id="eventModal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle" class="font-bold text-xl mb-2"></h2>
            <p><strong>Data:</strong> <span id="modalDate"></span></p>
            <p><strong>Godzina:</strong> <span id="modalTime"></span></p>
            <p><strong>Status:</strong> <span id="modalStatus"></span></p>
            <p><strong>Opis:</strong> <span id="modalDescription"></span></p>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://uicdn.toast.com/tui-calendar/latest/tui-calendar.js"></script>
    <script>
        $(document).ready(function() {
            var Calendar = tui.Calendar;
            var calendar = new Calendar('#calendar', {
                defaultView: 'month',
                taskView: false,
                scheduleView: ['time'],
                template: {
                    time: function(schedule) {
                        return schedule.title;
                    }
                }
            });

            // Dodanie wydarzeń z serwera
            var schedules = @json($events);
            schedules.forEach(function(event) {
                calendar.createSchedules([{
                    id: event.id,
                    calendarId: '1',
                    title: event.title,
                    category: 'time',
                    dueDateClass: '',
                    start: event.start,
                    end: event.end,
                    raw: event
                }]);
            });

            // Kliknięcie na termin
            calendar.on('clickSchedule', function(e) {
                var schedule = e.schedule.raw;
                $('#modalTitle').text(schedule.title);
                $('#modalDate').text(new Date(schedule.start).toLocaleDateString());
                $('#modalTime').text(
                    new Date(schedule.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) +
                    ' – ' +
                    new Date(schedule.end).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
                );
                $('#modalStatus').text(schedule.status);
                $('#modalDescription').text(schedule.description ?? 'Brak');

                $('#eventModal').fadeIn();
            });

            // Zamknięcie modala
            $('.close, #eventModal').on('click', function(e) {
                if (e.target !== this) return;
                $('#eventModal').fadeOut();
            });
        });
    </script>
@endsection
