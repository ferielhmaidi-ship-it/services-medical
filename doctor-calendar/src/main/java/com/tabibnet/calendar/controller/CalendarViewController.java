package com.tabibnet.calendar.controller;

import com.tabibnet.calendar.DataStore;
import com.tabibnet.calendar.model.CalendarSetting;
import com.tabibnet.calendar.model.Indisponibilite;
import com.tabibnet.calendar.model.TempsTravail;
import com.tabibnet.calendar.model.TimeSlot;
import com.tabibnet.calendar.service.CalendarService;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.ListView;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.time.temporal.TemporalAdjusters;
import java.util.List;

public class CalendarViewController {

    @FXML
    private Label weekLabel;

    @FXML
    private HBox weekGrid;

    private CalendarService calendarService;
    private LocalDate currentWeekStart;

    @FXML
    public void initialize() {
        calendarService = new CalendarService();
        // Start week on Monday
        currentWeekStart = LocalDate.now().with(TemporalAdjusters.previousOrSame(DayOfWeek.MONDAY));
        refreshCalendar();
    }

    @FXML
    private void previousWeek() {
        currentWeekStart = currentWeekStart.minusWeeks(1);
        refreshCalendar();
    }

    @FXML
    private void nextWeek() {
        currentWeekStart = currentWeekStart.plusWeeks(1);
        refreshCalendar();
    }

    private void refreshCalendar() {
        weekLabel.setText("Semaine du " + currentWeekStart.format(DateTimeFormatter.ofPattern("dd/MM/yyyy")));
        weekGrid.getChildren().clear();

        DataStore store = DataStore.getInstance();
        TempsTravail tt = store.getTempsTravail();
        CalendarSetting setting = store.getCalendarSetting();
        List<Indisponibilite> indisponibilites = store.getIndisponibilites();

        for (int i = 0; i < 7; i++) {
            LocalDate date = currentWeekStart.plusDays(i);
            VBox dayColumn = createDayColumn(date, tt, setting, indisponibilites);
            HBox.setHgrow(dayColumn, Priority.ALWAYS);
            weekGrid.getChildren().add(dayColumn);
        }
    }

    private VBox createDayColumn(LocalDate date, TempsTravail tt, CalendarSetting setting,
            List<Indisponibilite> indisponibilites) {
        VBox col = new VBox();
        col.setSpacing(10);
        col.setStyle("-fx-border-color: #ecf0f1; -fx-border-width: 0 1px 0 0; -fx-padding: 0 5px 0 5px;");
        col.setAlignment(Pos.TOP_CENTER);

        // Remove right border from last column
        if (date.getDayOfWeek() == DayOfWeek.SUNDAY) {
            col.setStyle("-fx-border-color: transparent; -fx-padding: 0 5px 0 5px;");
        }

        Label dayName = new Label(date.getDayOfWeek().name());
        dayName.setStyle("-fx-font-weight: bold; -fx-text-fill: #1f2f31;");

        Label dateLabel = new Label(date.format(DateTimeFormatter.ofPattern("dd/MM")));
        dateLabel.setStyle("-fx-text-fill: #7f8c8d;");

        col.getChildren().addAll(dayName, dateLabel);

        // Check if date is indisponible
        boolean isOff = indisponibilites.stream().anyMatch(ind -> ind.getDate().equals(date));

        // Check if date is in the past
        boolean isPast = date.isBefore(LocalDate.now());

        if (isOff) {
            Label offLabel = new Label("Jour de congé");
            offLabel.setStyle("-fx-text-fill: #e74c3c; -fx-font-weight: bold; -fx-padding: 20px 0;");

            Button cancelOffBtn = new Button("Annuler congé");
            cancelOffBtn.setStyle(
                    "-fx-background-color: transparent; -fx-text-fill: #7f8c8d; -fx-cursor: hand; -fx-underline: true;");
            if (isPast) {
                cancelOffBtn.setDisable(true);
            } else {
                cancelOffBtn.setOnAction(e -> {
                    DataStore.getInstance().removeIndisponibilite(date);
                    refreshCalendar();
                });
            }
            col.getChildren().addAll(offLabel, cancelOffBtn);
        } else {
            Button takeOffBtn = new Button("Prendre Congé");
            takeOffBtn.getStyleClass().add("btn-primary");
            takeOffBtn.setStyle("-fx-font-size: 11px; -fx-padding: 4px 8px;");

            if (isPast) {
                takeOffBtn.setDisable(true);
                takeOffBtn.setText("Date passée");
                takeOffBtn.setStyle("-fx-background-color: #bdc3c7; -fx-font-size: 11px; -fx-padding: 4px 8px;");
            } else {
                takeOffBtn.setOnAction(e -> {
                    DataStore.getInstance().addIndisponibilite(date);
                    refreshCalendar();
                });
            }
            col.getChildren().add(takeOffBtn);

            ListView<String> slotsView = new ListView<>();
            slotsView.getStyleClass().add("list-view");
            VBox.setVgrow(slotsView, Priority.ALWAYS);

            List<TimeSlot> slots = calendarService.generateSlots(date, tt, setting, indisponibilites);
            ObservableList<String> items = FXCollections.observableArrayList();
            if (slots.isEmpty()) {
                items.add("Aucun créneau");
            } else {
                for (TimeSlot s : slots) {
                    items.add(s.toString());
                }
            }
            slotsView.setItems(items);
            col.getChildren().add(slotsView);
        }

        return col;
    }
}
