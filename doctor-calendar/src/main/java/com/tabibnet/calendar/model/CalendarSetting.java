package com.tabibnet.calendar.model;

import java.time.LocalTime;

public class CalendarSetting {
    private int id;
    private int slotDuration;
    private LocalTime pauseStart;
    private LocalTime pauseEnd;
    private int doctorId;

    public CalendarSetting() {
    }

    public CalendarSetting(int id, int slotDuration, LocalTime pauseStart, LocalTime pauseEnd, int doctorId) {
        this.id = id;
        this.slotDuration = slotDuration;
        this.pauseStart = pauseStart;
        this.pauseEnd = pauseEnd;
        this.doctorId = doctorId;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getSlotDuration() { return slotDuration; }
    public void setSlotDuration(int slotDuration) { this.slotDuration = slotDuration; }

    public LocalTime getPauseStart() { return pauseStart; }
    public void setPauseStart(LocalTime pauseStart) { this.pauseStart = pauseStart; }

    public LocalTime getPauseEnd() { return pauseEnd; }
    public void setPauseEnd(LocalTime pauseEnd) { this.pauseEnd = pauseEnd; }

    public int getDoctorId() { return doctorId; }
    public void setDoctorId(int doctorId) { this.doctorId = doctorId; }
}
