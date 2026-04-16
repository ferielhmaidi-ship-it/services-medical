package com.tabibnet.calendar.model;

import java.time.LocalDate;
import java.time.LocalTime;

public class TempsTravail {
    private int id;
    private String dayOfWeek;
    private LocalTime startTime;
    private LocalTime endTime;
    private int doctorId;
    private LocalDate specificDate;

    public TempsTravail() {
    }

    public TempsTravail(int id, String dayOfWeek, LocalTime startTime, LocalTime endTime, int doctorId, LocalDate specificDate) {
        this.id = id;
        this.dayOfWeek = dayOfWeek;
        this.startTime = startTime;
        this.endTime = endTime;
        this.doctorId = doctorId;
        this.specificDate = specificDate;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getDayOfWeek() { return dayOfWeek; }
    public void setDayOfWeek(String dayOfWeek) { this.dayOfWeek = dayOfWeek; }

    public LocalTime getStartTime() { return startTime; }
    public void setStartTime(LocalTime startTime) { this.startTime = startTime; }

    public LocalTime getEndTime() { return endTime; }
    public void setEndTime(LocalTime endTime) { this.endTime = endTime; }

    public int getDoctorId() { return doctorId; }
    public void setDoctorId(int doctorId) { this.doctorId = doctorId; }

    public LocalDate getSpecificDate() { return specificDate; }
    public void setSpecificDate(LocalDate specificDate) { this.specificDate = specificDate; }
}
