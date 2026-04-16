package com.tabibnet.calendar.controller;

import com.tabibnet.calendar.DataStore;
import com.tabibnet.calendar.model.CalendarSetting;
import com.tabibnet.calendar.model.TempsTravail;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;

import java.time.LocalTime;
import java.time.format.DateTimeParseException;

public class SettingsController {

    @FXML
    private TextField startTimeField;
    @FXML
    private TextField endTimeField;
    @FXML
    private TextField slotDurationField;
    @FXML
    private TextField pauseStartField;
    @FXML
    private TextField pauseEndField;
    @FXML
    private Label statusLabel;

    @FXML
    public void initialize() {
        DataStore store = DataStore.getInstance();
        TempsTravail tt = store.getTempsTravail();
        CalendarSetting setting = store.getCalendarSetting();

        if (tt != null) {
            startTimeField.setText(tt.getStartTime().toString());
            endTimeField.setText(tt.getEndTime().toString());
        }

        if (setting != null) {
            slotDurationField.setText(String.valueOf(setting.getSlotDuration()));
            pauseStartField.setText(setting.getPauseStart() != null ? setting.getPauseStart().toString() : "");
            pauseEndField.setText(setting.getPauseEnd() != null ? setting.getPauseEnd().toString() : "");
        }
    }

    @FXML
    private void saveSettings() {
        try {
            LocalTime start = LocalTime.parse(startTimeField.getText());
            LocalTime end = LocalTime.parse(endTimeField.getText());
            int duration = Integer.parseInt(slotDurationField.getText());

            LocalTime pauseStart = null;
            LocalTime pauseEnd = null;
            if (!pauseStartField.getText().isEmpty()) {
                pauseStart = LocalTime.parse(pauseStartField.getText());
            }
            if (!pauseEndField.getText().isEmpty()) {
                pauseEnd = LocalTime.parse(pauseEndField.getText());
            }

            DataStore store = DataStore.getInstance();

            TempsTravail tt = store.getTempsTravail();
            tt.setStartTime(start);
            tt.setEndTime(end);

            CalendarSetting setting = store.getCalendarSetting();
            setting.setSlotDuration(duration);
            setting.setPauseStart(pauseStart);
            setting.setPauseEnd(pauseEnd);

            store.updateSettings(tt, setting);

            statusLabel.setText("Settings saved successfully to the database!");
            statusLabel.setStyle("-fx-text-fill: #27ae60;"); // success green
        } catch (DateTimeParseException | NumberFormatException e) {
            statusLabel.setText("Error: Invalid time or number format. Use HH:mm");
            statusLabel.setStyle("-fx-text-fill: #c0392b;"); // error red
        }
    }
}
