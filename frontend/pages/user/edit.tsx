import { Button, Container, TextField } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { Autocomplete } from '@material-ui/lab';
import { useFormik } from 'formik';
import * as yup from 'yup';
import React from 'react';
import { DropzoneDialog } from 'material-ui-dropzone';
import Head from 'next/head';
import Link from 'next/link';
import axiosClient from '../../src/api';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../src/server/fetchUser';
import { UserProfile } from '../../src/types/userProfile';
import { useRouter } from 'next/router';
import { fetchUserProfile } from '../../src/server/userProfile';
import { useTranslation } from 'next-i18next';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { User } from '../../src/types/user';

interface IFormValues {
  readonly firstName: string;
  readonly lastName: string;
  readonly image?: string;
  readonly biography?: string;
  readonly skills: string[];
  readonly references: string[];
}

interface UserProfileEditProps {
  readonly user: User;
  readonly userProfile: UserProfile;
  readonly cookies: Record<string, string>;
}

const useStyles = makeStyles((theme) => ({
  formField: {
    marginTop: theme.spacing(2),
    marginBottom: theme.spacing(2),
  },
  cancelButton: {
    marginLeft: theme.spacing(2),
  },
}));

export async function getServerSideProps(
  context: GetServerSidePropsContext,
): Promise<GetServerSidePropsResult<UserProfileEditProps>> {
  const user = await fetchUser(context.req.cookies);

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  let userProfile = await fetchUserProfile(user.id, context.req.cookies);

  // TODO: user profile should be created during first login/registration
  if (userProfile === null) {
    userProfile = {
      id: user.id,
      firstName: '',
      lastName: '',
      skills: [],
      references: [],
    };
  }

  return {
    props: {
      user,
      cookies: context.req.cookies,
      userProfile,
      ...(await serverSideTranslations('de', ['common', 'user-edit'])),
    },
  };
}

export default function EditUserProfile({ user, userProfile }: UserProfileEditProps): JSX.Element {
  const { t: tCommon } = useTranslation('common');
  const { t: tUserEdit } = useTranslation('user-edit');

  const router = useRouter();
  const [dropzoneOpen, setDropzoneOpen] = React.useState(false);
  const validationSchema = yup.object({
    firstName: yup.string().required(tUserEdit('messageFirstNameRequired')),
    lastName: yup.string().required(tUserEdit('messageLastNameRequired')),
    biography: yup.string().nullable(),
    skills: yup.array().ensure(),
    references: yup.array().ensure(),
  });

  const submitForm = (values: IFormValues): void => {
    axiosClient
      .put(`/user_profile`, values)
      .then(() => router.push(`/user/${user.id}`))
      .catch((error) => {
        console.log(error);
      });
  };

  const uploadFile = async (file: ArrayBuffer): Promise<string> => {
    const result = await axiosClient.post(`/file`, file);
    return result.data.id;
    // TODO: success message/back navigation? error handling?
  };

  const formik = useFormik<IFormValues>({
    initialValues: {
      ...userProfile,
    },
    validationSchema: validationSchema,
    onSubmit: (values) => {
      console.log('submitting user profile!');
      submitForm(values);
    },
  });

  const classes = useStyles();

  return (
    <>
      <Head>
        <title>
          {tUserEdit('title')} - {tCommon('appName')}
        </title>
      </Head>
      <Container>
        <h1>Ihr Profil</h1>
        <form
          noValidate
          onSubmit={(e) => {
            e.preventDefault();
            formik.submitForm();
          }}
        >
          <TextField
            label={tUserEdit('firstName')}
            name="firstName"
            fullWidth
            className={classes.formField}
            value={formik.values.firstName}
            onChange={formik.handleChange}
            helperText={formik.touched.firstName && formik.errors.firstName}
            error={formik.touched.firstName && Boolean(formik.errors.firstName)}
            onBlur={formik.handleBlur}
            required
          />
          <TextField
            label={tUserEdit('lastName')}
            name="lastName"
            fullWidth
            multiline
            className={classes.formField}
            value={formik.values.lastName}
            onChange={formik.handleChange}
            helperText={formik.touched.lastName && formik.errors.lastName}
            error={formik.touched.lastName && Boolean(formik.errors.lastName)}
            onBlur={formik.handleBlur}
            required
          />
          <TextField
            label={tUserEdit('biography')}
            name="biography"
            fullWidth
            multiline
            className={classes.formField}
            value={formik.values.biography}
            onChange={formik.handleChange}
            helperText={formik.touched.biography && formik.errors.biography}
            error={formik.touched.biography && Boolean(formik.errors.biography)}
            onBlur={formik.handleBlur}
          />
          <Autocomplete
            id="skills"
            className={classes.formField}
            multiple
            freeSolo
            value={formik.values.skills}
            onChange={(e, values) => formik.setFieldValue('skills', values)}
            options={skillsOptions}
            getOptionLabel={(option) => option}
            defaultValue={[]}
            onBlur={formik.handleBlur}
            renderInput={(params) => (
              <TextField
                {...params}
                variant="standard"
                label={tUserEdit('skills')}
                helperText={formik.touched.skills && formik.errors.skills}
                error={formik.touched.skills && Boolean(formik.errors.skills)}
              />
            )}
          />
          <Autocomplete
            id="references"
            className={classes.formField}
            multiple
            freeSolo
            value={formik.values.references}
            onChange={(e, values) => formik.setFieldValue('references', values)}
            options={skillsOptions}
            getOptionLabel={(option) => option}
            defaultValue={[]}
            onBlur={formik.handleBlur}
            renderInput={(params) => (
              <TextField
                {...params}
                variant="standard"
                label={tUserEdit('references')}
                helperText={formik.touched.references && formik.errors.references}
                error={formik.touched.references && Boolean(formik.errors.references)}
              />
            )}
          />
          <Button variant="contained" color="primary" onClick={() => setDropzoneOpen(true)}>
            Add Image
          </Button>

          <DropzoneDialog
            acceptedFiles={['image/*']}
            cancelButtonText={'cancel'}
            submitButtonText={'submit'}
            maxFileSize={5000000}
            filesLimit={1}
            open={dropzoneOpen}
            onClose={() => setDropzoneOpen(false)}
            onSave={(files) => {
              if (files.length > 0) {
                files[0]
                  .arrayBuffer()
                  .then((buffer) => uploadFile(buffer))
                  .then((id) => {
                    console.log('Image id: ' + id);
                    formik.setFieldValue('image', id);
                  })
                  .catch((error) => console.log(error));
              }
              setDropzoneOpen(false);
            }}
            showPreviews={true}
            showFileNamesInPreview={true}
          />
          <Button variant="contained" color="primary" type="submit">
            {tUserEdit('buttonSubmit')}
          </Button>
          <Link href={`/user/${userProfile.id}`}>
            <Button className={classes.cancelButton} variant="contained" color="default" type="button">
              {tUserEdit('buttonCancel')}
            </Button>
          </Link>
        </form>
      </Container>
    </>
  );
}

const skillsOptions: string[] = []; // TODO: hard coded defaults or values loaded from the backend?
