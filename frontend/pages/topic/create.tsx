import { Button, Container, TextField } from "@material-ui/core";
import { makeStyles } from '@material-ui/core/styles';
import { Autocomplete, Value } from "@material-ui/lab";
import { KeyboardDatePicker } from "@material-ui/pickers";
import React, { ChangeEvent } from "react";


const useStyles = makeStyles((theme) => ({
    formField: {
        marginTop: theme.spacing(2),
        marginBottom: theme.spacing(2)
    },
}));

function create() {
    const [title, setTitle] = React.useState("");
    const handleTitleChange = (e: ChangeEvent<HTMLTextAreaElement | HTMLInputElement>) => {
        setTitle(e.target.value);
    }
    const [description, setDescription] = React.useState("");
    const handleDescriptionChange = (e: ChangeEvent<HTMLTextAreaElement | HTMLInputElement>) => {
        setDescription(e.target.value);
    }
    const [requirements, setRequirements] = React.useState("");
    const handleRequirementsChange = (e: ChangeEvent<HTMLTextAreaElement | HTMLInputElement>) => {
        setRequirements(e.target.value);
    }
    const [deadline, setDeadline] = React.useState(null);
    const [tags, setTags] = React.useState([]);
    const handleTagesChange = (event: React.ChangeEvent<{}>, values: string[]) => {
        setTags(values)
    };

    const submitForm = async (e) => {
        e.preventDefault();
        const topicData = {
            title: title,
            description: description,
            requirements: requirements,
            deadline: setDeadline,
            tags: tags
        }
        const response = await fetch('/topic', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(topicData),
        });
        // TODO: success message/back navigation? error handling?
    };

    const classes = useStyles();
    return <Container>
        <h1>Neues Thema erstellen</h1>
        <form autoComplete="off" onSubmit={submitForm}>
            <TextField label="Titel" fullWidth className={classes.formField} value={title} onChange={handleTitleChange} />
            <TextField label="Beschreibung" fullWidth multiline className={classes.formField} onChange={handleDescriptionChange} />
            <TextField label="Anforderungen" fullWidth multiline className={classes.formField} onChange={handleRequirementsChange} />
            <KeyboardDatePicker
          disableToolbar
          variant="inline"
          format="dd/MM/yyyy"
          margin="normal"
          id="date-picker-inline"
          label="Endtermin"
          value={deadline}
          onChange={setDeadline}
          KeyboardButtonProps={{
            'aria-label': 'change date',
          }}
        />
            <Autocomplete
                className={classes.formField}
                multiple
                freeSolo
                value={tags}
                onChange={handleTagesChange}
                id="tags-standard"
                options={tagOptions}
                getOptionLabel={(option) => option}
                defaultValue={[]}
                renderInput={(params) => (
                    <TextField
                        {...params}
                        variant="standard"
                        label="Tags"
                    />
                )}
            />
            <Button variant="contained" color="primary" type="submit">
                Erstellen
            </Button>
        </form>
    </Container>;
}

const tagOptions: string[] = [];// TODO: hard coded defaults or values loaded from the backend?

export default create;