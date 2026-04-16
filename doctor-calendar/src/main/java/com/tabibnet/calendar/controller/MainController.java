package com.tabibnet.calendar.controller;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.layout.AnchorPane;
import javafx.scene.layout.BorderPane;
import javafx.scene.layout.VBox;

import java.io.IOException;

public class MainController {

    @FXML
    private BorderPane mainBorderPane;

    @FXML
    public void initialize() {
        loadView("CalendarView");
    }

    @FXML
    private void showCalendar() {
        loadView("CalendarView");
    }

    @FXML
    private void showSettings() {
        loadView("SettingsView");
    }

    private void loadView(String v) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/" + v + ".fxml"));
            AnchorPane view = loader.load();
            mainBorderPane.setCenter(view);
            
            // Allow child controllers to access root or DataStore if needed.
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
