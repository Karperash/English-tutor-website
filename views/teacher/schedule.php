<?php $title = 'Расписание'; ?>

<div class="page-head">
    <div>
        <span class="eyebrow">Расписание</span>
        <h1>Расписание занятий</h1>
        <p>Назначайте ученикам дату и время занятий.</p>
    </div>
</div>

<div class="content-grid schedule-layout">

    <section class="panel sticky-panel">

        <div class="panel-head">
            <h2>Назначить занятие</h2>
        </div>

        <form
            method="post"
            action="/teacher/schedule/lessons"
            class="stack-form"
        >

            <?= csrf_field() ?>

            <label class="field">
                <span>Ученик</span>

                <select name="student_id" required>
                    <option value="">Выберите ученика</option>

                    <?php foreach ($students as $student): ?>
                        <option value="<?= (int)$student['id'] ?>">
                            <?= e(
                                $student['first_name']
                                . ' '
                                . $student['last_name']
                            ) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </label>

            <label class="field">
                <span>Дата</span>

                <input
                    type="date"
                    name="date"
                    min="<?= date('Y-m-d') ?>"
                    required
                >
            </label>

            <label class="field">
                <span>Время начала</span>

                <input
                    type="time"
                    name="time"
                    step="900"
                    required
                >
            </label>

            <button
                class="button button-full"
                type="submit"
            >
                Назначить занятие
            </button>

        </form>

        <div class="info-box">
            Занятие длится 60 минут.
            После назначения оно автоматически появится
            в расписании ученика.
        </div>

    </section>


    <section class="panel">

        <div class="panel-head">
            <h2>Ближайшие занятия</h2>
        </div>

        <?php if (!$lessons): ?>

            <p class="empty">
                Занятий пока нет. Назначьте первое занятие.
            </p>

        <?php endif; ?>


        <div class="schedule-list">

            <?php
            $lastDate = null;

            foreach ($lessons as $lesson):

                $date = date(
                    'Y-m-d',
                    strtotime($lesson['starts_at'])
                );
            ?>

                <?php if ($date !== $lastDate): ?>

                    <?php $lastDate = $date; ?>

                    <div class="schedule-date">

                        <strong>
                            <?= date(
                                'd.m.Y',
                                strtotime($lesson['starts_at'])
                            ) ?>
                        </strong>

                        <span>
                            <?= e(
                                [
                                    'Sun' => 'Вс',
                                    'Mon' => 'Пн',
                                    'Tue' => 'Вт',
                                    'Wed' => 'Ср',
                                    'Thu' => 'Чт',
                                    'Fri' => 'Пт',
                                    'Sat' => 'Сб',
                                ][
                                    date(
                                        'D',
                                        strtotime($lesson['starts_at'])
                                    )
                                ] ?? ''
                            ) ?>
                        </span>

                    </div>

                <?php endif; ?>


                <div class="schedule-row is-booked">

                    <div class="schedule-time">

                        <?= date(
                            'H:i',
                            strtotime($lesson['starts_at'])
                        ) ?>

                        <small>
                            —
                            <?= date(
                                'H:i',
                                strtotime($lesson['ends_at'])
                            ) ?>
                        </small>

                    </div>


                    <div class="schedule-info">

                        <strong>
                            <?= e(
                                $lesson['first_name']
                                . ' '
                                . $lesson['last_name']
                            ) ?>
                        </strong>

                        <small>
                            Запланировано ·
                            <a href="/teacher/lessons/<?= (int)$lesson['id'] ?>">
                                открыть занятие
                            </a>
                        </small>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </section>

</div>