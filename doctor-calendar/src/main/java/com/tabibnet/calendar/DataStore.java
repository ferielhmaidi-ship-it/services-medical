package com.tabibnet.calendar;

import com.tabibnet.calendar.model.CalendarSetting;
import com.tabibnet.calendar.model.Indisponibilite;
import com.tabibnet.calendar.model.TempsTravail;

import java.sql.*;
import java.time.LocalDate;
import java.time.LocalTime;
import java.util.ArrayList;
import java.util.List;

public class DataStore {
    private static DataStore instance;

    // Based on symfony .env (XAMPP Environment)
    private static final String URL = "jdbc:mysql://localhost:3306/tabibnet";
    private static final String USER = "root";
    private static final String PASS = "";

    private TempsTravail tempsTravail;
    private CalendarSetting calendarSetting;
    private List<Indisponibilite> indisponibilites;

    private DataStore() {
        loadFromDatabase();
    }

    public static DataStore getInstance() {
        if (instance == null) {
            instance = new DataStore();
        }
        return instance;
    }

    public void loadFromDatabase() {
        indisponibilites = new ArrayList<>();

        try (Connection conn = DriverManager.getConnection(URL, USER, PASS)) {
            // Load Indisponibilites
            try (PreparedStatement stmt = conn.prepareStatement("SELECT * FROM indisponibilite WHERE doctor_id = 1")) {
                ResultSet rs = stmt.executeQuery();
                while (rs.next()) {
                    boolean isEmergency = false;
                    try {
                        isEmergency = rs.getBoolean("is_emergency");
                    } catch (Exception e) {
                        /* column check fallback */ }
                    indisponibilites.add(new Indisponibilite(
                            rs.getInt("id"),
                            rs.getDate("date").toLocalDate(),
                            rs.getInt("doctor_id"),
                            isEmergency));
                }
            }

            // Load TempsTravail (first/default one for simplicity)
            try (PreparedStatement stmt = conn
                    .prepareStatement("SELECT * FROM temps_travail WHERE doctor_id = 1 LIMIT 1")) {
                ResultSet rs = stmt.executeQuery();
                if (rs.next()) {
                    tempsTravail = new TempsTravail(
                            rs.getInt("id"),
                            rs.getString("day_of_week"),
                            rs.getTime("start_time").toLocalTime(),
                            rs.getTime("end_time").toLocalTime(),
                            rs.getInt("doctor_id"),
                            null);
                } else {
                    tempsTravail = new TempsTravail(1, "Monday", LocalTime.of(8, 0), LocalTime.of(17, 0), 1, null);
                }
            }

            // Load CalendarSetting
            try (PreparedStatement stmt = conn
                    .prepareStatement("SELECT * FROM calendar_setting WHERE doctor_id = 1 LIMIT 1")) {
                ResultSet rs = stmt.executeQuery();
                if (rs.next()) {
                    Time pStart = rs.getTime("pause_start");
                    Time pEnd = rs.getTime("pause_end");
                    calendarSetting = new CalendarSetting(
                            rs.getInt("id"),
                            rs.getInt("slot_duration"),
                            pStart != null ? pStart.toLocalTime() : null,
                            pEnd != null ? pEnd.toLocalTime() : null,
                            rs.getInt("doctor_id"));
                } else {
                    calendarSetting = new CalendarSetting(1, 30, LocalTime.of(12, 0), LocalTime.of(13, 0), 1);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
            // Fallback mock data if network fails
            if (tempsTravail == null)
                tempsTravail = new TempsTravail(1, "Monday", LocalTime.of(8, 0), LocalTime.of(17, 0), 1, null);
            if (calendarSetting == null)
                calendarSetting = new CalendarSetting(1, 30, LocalTime.of(12, 0), LocalTime.of(13, 0), 1);
        }
    }

    public TempsTravail getTempsTravail() {
        return tempsTravail;
    }

    public CalendarSetting getCalendarSetting() {
        return calendarSetting;
    }

    public List<Indisponibilite> getIndisponibilites() {
        return indisponibilites;
    }

    public void addIndisponibilite(LocalDate date) {
        try (Connection conn = DriverManager.getConnection(URL, USER, PASS);
                PreparedStatement stmt = conn.prepareStatement(
                        "INSERT INTO indisponibilite (date, doctor_id, is_emergency) VALUES (?, ?, ?)")) {
            stmt.setDate(1, Date.valueOf(date));
            stmt.setInt(2, 1);
            stmt.setBoolean(3, false);
            stmt.executeUpdate();
            loadFromDatabase();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void removeIndisponibilite(LocalDate date) {
        try (Connection conn = DriverManager.getConnection(URL, USER, PASS);
                PreparedStatement stmt = conn
                        .prepareStatement("DELETE FROM indisponibilite WHERE date = ? AND doctor_id = ?")) {
            stmt.setDate(1, Date.valueOf(date));
            stmt.setInt(2, 1);
            stmt.executeUpdate();
            loadFromDatabase();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void updateSettings(TempsTravail tt, CalendarSetting setting) {
        try (Connection conn = DriverManager.getConnection(URL, USER, PASS)) {
            // Check if TempsTravail exists
            try (PreparedStatement check = conn.prepareStatement("SELECT id FROM temps_travail WHERE doctor_id = 1")) {
                if (check.executeQuery().next()) {
                    try (PreparedStatement stmt = conn
                            .prepareStatement("UPDATE temps_travail SET start_time=?, end_time=? WHERE doctor_id=1")) {
                        stmt.setTime(1, Time.valueOf(tt.getStartTime()));
                        stmt.setTime(2, Time.valueOf(tt.getEndTime()));
                        stmt.executeUpdate();
                    }
                } else {
                    try (PreparedStatement stmt = conn.prepareStatement(
                            "INSERT INTO temps_travail (day_of_week, start_time, end_time, doctor_id) VALUES ('Monday', ?, ?, 1)")) {
                        stmt.setTime(1, Time.valueOf(tt.getStartTime()));
                        stmt.setTime(2, Time.valueOf(tt.getEndTime()));
                        stmt.executeUpdate();
                    }
                }
            }

            // Check if CalendarSetting exists
            try (PreparedStatement check = conn
                    .prepareStatement("SELECT id FROM calendar_setting WHERE doctor_id = 1")) {
                if (check.executeQuery().next()) {
                    try (PreparedStatement stmt = conn.prepareStatement(
                            "UPDATE calendar_setting SET slot_duration=?, pause_start=?, pause_end=? WHERE doctor_id=1")) {
                        stmt.setInt(1, setting.getSlotDuration());
                        stmt.setTime(2, setting.getPauseStart() != null ? Time.valueOf(setting.getPauseStart()) : null);
                        stmt.setTime(3, setting.getPauseEnd() != null ? Time.valueOf(setting.getPauseEnd()) : null);
                        stmt.executeUpdate();
                    }
                } else {
                    try (PreparedStatement stmt = conn.prepareStatement(
                            "INSERT INTO calendar_setting (slot_duration, pause_start, pause_end, doctor_id) VALUES (?, ?, ?, 1)")) {
                        stmt.setInt(1, setting.getSlotDuration());
                        stmt.setTime(2, setting.getPauseStart() != null ? Time.valueOf(setting.getPauseStart()) : null);
                        stmt.setTime(3, setting.getPauseEnd() != null ? Time.valueOf(setting.getPauseEnd()) : null);
                        stmt.executeUpdate();
                    }
                }
            }
            loadFromDatabase();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
