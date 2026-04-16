package com.tabibnet.calendar.model;

import java.time.LocalDate;

public class Indisponibilite {
    private int id;
    private LocalDate date;
    private int doctorId;
    private boolean isEmergency;

    public Indisponibilite() {
    }

    public Indisponibilite(int id, LocalDate date, int doctorId, boolean isEmergency) {
        this.id = id;
        this.date = date;
        this.doctorId = doctorId;
        this.isEmergency = isEmergency;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public LocalDate getDate() { return date; }
    public void setDate(LocalDate date) { this.date = date; }

    public int getDoctorId() { return doctorId; }
    public void setDoctorId(int doctorId) { this.doctorId = doctorId; }

    public boolean isEmergency() { return isEmergency; }
    public void setEmergency(boolean emergency) { isEmergency = emergency; }
}
